<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnalysisRequest;
use App\Jobs\ProcessPdfAnalysisJob;
use App\Models\AnalysisBatch;
use App\Models\Document;
use App\Services\PdfAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    public function index(): View
    {
        return view('analysis.index');
    }

    public function initBatch(StoreAnalysisRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::debug('Initializing Batch', ['total' => $request->input('total_files')]);
        
        $keywords = $request->keywords();
        $totalFiles = (int) $request->input('total_files', 0);

        $analysisBatch = DB::transaction(function () use ($keywords, $totalFiles, $request) {
            $batch = AnalysisBatch::create([
                'status' => AnalysisBatch::STATUS_PROCESSING,
                'total_documents' => $totalFiles,
                'word_limit' => max(10, min(100, (int) $request->input('word_limit', 50))),
            ]);

            foreach ($keywords as $keyword) {
                $batch->keywords()->create(['keyword' => $keyword]);
            }

            return $batch;
        });

        return response()->json([
            'id' => $analysisBatch->id,
            'redirect' => route('analysis.show', $analysisBatch),
        ]);
    }

    public function uploadDocument(AnalysisBatch $analysisBatch, \Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $document = $request->file('document');

        $checksum = null;
        if (is_string($document->getRealPath()) && file_exists($document->getRealPath())) {
            $checksum = hash_file('sha256', $document->getRealPath()) ?: null;
        }

        $storedPath = $document->store('analysis/' . $analysisBatch->id);

        $metadata = [
            'upload' => [
                'original_name' => $document->getClientOriginalName(),
                'size_bytes' => $document->getSize(),
                'mime_type' => $document->getClientMimeType(),
                'checksum_sha256' => $checksum,
                'uploaded_at' => now()->toIso8601String(),
            ],
        ];

        $record = $analysisBatch->documents()->create([
            'original_filename' => $document->getClientOriginalName(),
            'stored_path' => $storedPath,
            'file_size_bytes' => $document->getSize(),
            'mime_type' => $document->getClientMimeType(),
            'checksum_sha256' => $checksum,
            'status' => Document::STATUS_QUEUED,
            'error_message' => null,
            'metadata' => $metadata,
        ]);

        ProcessPdfAnalysisJob::dispatch($record->id);

        return response()->json([
            'success' => true,
            'document_id' => $record->id,
        ]);
    }

    public function show(AnalysisBatch $analysisBatch): View
    {
        $analysisBatch->load(['documents.keywordResults', 'keywords']);

        $results = [];
        foreach ($analysisBatch->documents as $document) {
            foreach ($document->keywordResults as $result) {
                $results[$document->id][$result->keyword_id] = $result->frequency_count;
            }
        }

        $progress = $this->batchProgress($analysisBatch);

        return view('analysis.show', [
            'analysisBatch' => $analysisBatch,
            'results' => $results,
            'total' => $progress['total'],
            'processed' => $progress['processed'],
            'completed' => $progress['completed'],
            'failed' => $progress['failed'],
            'pending' => $progress['pending'],
        ]);
    }

    public function progress(AnalysisBatch $analysisBatch): JsonResponse
    {
        $analysisBatch->load(['documents.keywordResults']);

        $progress = $this->batchProgress($analysisBatch);
        
        // Auto-fix: Trigger aggregation if documents are done but batch is stuck
        if ($progress['total'] > 0 && $progress['processed'] >= $progress['total'] && $analysisBatch->status === AnalysisBatch::STATUS_PROCESSING) {
            $analysisBatch->aggregateIntelligence();
        }

        $documents = $analysisBatch->documents->map(function ($doc) {
            $docResults = [];
            foreach ($doc->keywordResults as $res) {
                $docResults[$res->keyword_id] = $res->frequency_count;
            }
            return [
                'id' => $doc->id,
                'status' => $doc->status,
                'error' => $doc->error_message,
                'top_words' => $doc->top_words,
                'metadata' => $doc->metadata,
                'file_size_bytes' => $doc->file_size_bytes,
                'mime_type' => $doc->mime_type,
                'checksum_sha256' => $doc->checksum_sha256,
                'page_count' => $doc->page_count,
                'analyzed_at' => optional($doc->analyzed_at)->toIso8601String(),
                'results' => $docResults,
            ];
        });

        return response()->json([
            'processed' => $progress['processed'],
            'total' => $progress['total'],
            'completed' => $progress['completed'],
            'failed' => $progress['failed'],
            'pending' => $progress['pending'],
            'is_complete' => $progress['total'] > 0 && $progress['processed'] >= $progress['total'],
            'word_limit' => $analysisBatch->word_limit,
            'top_words' => $analysisBatch->top_words,
            'documents' => $documents,
        ]);
    }

    public function documentTopWords(AnalysisBatch $analysisBatch, Document $document, Request $request): JsonResponse
    {
        abort_unless($document->analysis_batch_id === $analysisBatch->id, 404);

        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($validated['limit'] ?? 50);
        $topWords = $this->sliceWordMap($document->top_words ?? [], $limit);

        return response()->json([
            'document' => [
                'id' => $document->id,
                'filename' => $document->original_filename,
                'status' => $document->status,
                'word_count' => data_get($document->metadata, 'analysis.word_count'),
                'unique_word_count' => data_get($document->metadata, 'analysis.unique_word_count'),
                'page_count' => $document->page_count,
            ],
            'limit' => $limit,
            'available' => count($document->top_words ?? []),
            'top_words' => $topWords,
        ]);
    }

    public function compareProposal(AnalysisBatch $analysisBatch, Request $request, PdfAnalysisService $analysisService): JsonResponse
    {
        $request->validate([
            'proposal' => ['required', 'file', 'mimes:pdf,txt,md', 'max:20480'],
        ]);

        $proposal = $request->file('proposal');
        $path = $proposal?->getRealPath();

        if (! is_string($path) || ! file_exists($path)) {
            throw ValidationException::withMessages([
                'proposal' => 'The proposal file could not be read.',
            ]);
        }

        $proposalText = $this->extractProposalText($path, strtolower($proposal->getClientOriginalExtension()), $analysisService);

        if (trim($proposalText) === '') {
            throw ValidationException::withMessages([
                'proposal' => 'The proposal does not contain readable text.',
            ]);
        }

        $analysisBatch->load(['keywords', 'documents.keywordResults.keyword']);

        $proposalWords = $this->normalizeWordWeights(
            $analysisService->generateWordIntelligence($proposalText, 100)['words'],
            100
        );
        $keywordValues = $analysisBatch->keywords->pluck('keyword')->all();
        $proposalKeywordCounts = $analysisService->countKeywords($proposalText, $keywordValues);

        $documents = $analysisBatch->documents
            ->where('status', Document::STATUS_COMPLETED)
            ->map(function (Document $document) use ($proposalWords, $proposalKeywordCounts) {
                $comparison = $this->scoreDocumentSimilarity($proposalWords, $proposalKeywordCounts, $document);

                return [
                    'id' => $document->id,
                    'filename' => $document->original_filename,
                    'score' => $comparison['score'],
                    'verdict' => $comparison['verdict'],
                    'shared_words' => $comparison['shared_words'],
                    'keyword_matches' => $comparison['keyword_matches'],
                    'word_count' => data_get($document->metadata, 'analysis.word_count'),
                    'page_count' => $document->page_count,
                ];
            })
            ->sortByDesc('score')
            ->values();

        if ($documents->isEmpty()) {
            return response()->json([
                'summary' => [
                    'verdict' => 'No completed papers yet',
                    'suitable' => false,
                    'score' => 0,
                    'message' => 'Complete at least one paper analysis before comparing a proposal.',
                ],
                'proposal_top_words' => $this->sliceWordMap($proposalWords, 25),
                'proposal_keyword_counts' => $proposalKeywordCounts,
                'documents' => [],
            ]);
        }

        $bestScore = (int) $documents->max('score');
        $topAverage = (int) round($documents->take(3)->avg('score'));
        $summary = $this->proposalSummary($bestScore, $topAverage, $documents->first());

        return response()->json([
            'summary' => $summary,
            'proposal_top_words' => $this->sliceWordMap($proposalWords, 25),
            'proposal_keyword_counts' => $proposalKeywordCounts,
            'documents' => $documents,
        ]);
    }

    /**
     * @return array{total:int,completed:int,failed:int,processed:int,pending:int}
     */
    private function batchProgress(AnalysisBatch $analysisBatch): array
    {
        $total = max((int) $analysisBatch->total_documents, (int) $analysisBatch->documents->count());
        $completed = $analysisBatch->documents->where('status', Document::STATUS_COMPLETED)->count();
        $failed = $analysisBatch->documents->where('status', Document::STATUS_FAILED)->count();
        $processed = $completed + $failed;

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'processed' => $processed,
            'pending' => max(0, $total - $processed),
        ];
    }

    private function extractProposalText(string $path, string $extension, PdfAnalysisService $analysisService): string
    {
        if ($extension === 'pdf') {
            return $analysisService->extractText($path);
        }

        $contents = file_get_contents($path);

        return is_string($contents) ? $contents : '';
    }

    /**
     * @param array<string, mixed> $words
     * @return array<string, int>
     */
    private function normalizeWordWeights(array $words, int $limit = 100): array
    {
        $normalized = [];

        foreach ($words as $word => $count) {
            $key = mb_strtolower(trim((string) $word));
            $key = preg_replace('/\s+/', ' ', $key) ?: '';

            if ($key === '') {
                continue;
            }

            $normalized[$key] = ($normalized[$key] ?? 0) + max(0, (int) $count);
        }

        arsort($normalized);

        return array_slice($normalized, 0, max(1, min(100, $limit)), true);
    }

    /**
     * @param array<string, mixed> $words
     * @return array<string, int>
     */
    private function sliceWordMap(array $words, int $limit): array
    {
        $normalized = $this->normalizeWordWeights($words, $limit);

        return array_slice($normalized, 0, max(1, min(100, $limit)), true);
    }

    /**
     * @param array<string, int> $proposalWords
     * @param array<string, int> $proposalKeywordCounts
     * @return array{score:int,verdict:string,shared_words:array<int, array{word:string,proposal_count:int,paper_count:int}>,keyword_matches:array<int, string>}
     */
    private function scoreDocumentSimilarity(array $proposalWords, array $proposalKeywordCounts, Document $document): array
    {
        $documentWords = $this->normalizeWordWeights($document->top_words ?? [], 100);
        $shared = [];
        $sharedWeight = 0;

        foreach ($proposalWords as $word => $proposalCount) {
            if (! isset($documentWords[$word])) {
                continue;
            }

            $paperCount = $documentWords[$word];
            $sharedWeight += min($proposalCount, $paperCount);
            $shared[] = [
                'word' => $word,
                'proposal_count' => $proposalCount,
                'paper_count' => $paperCount,
            ];
        }

        usort($shared, static fn (array $a, array $b): int => min($b['proposal_count'], $b['paper_count']) <=> min($a['proposal_count'], $a['paper_count']));

        $proposalWeight = max(1, array_sum($proposalWords));
        $documentWeight = max(1, array_sum($documentWords));
        $weightedOverlap = $sharedWeight / max(1, min($proposalWeight, $documentWeight));
        $termOverlap = count($shared) / max(1, min(50, count($proposalWords), count($documentWords)));

        $documentKeywordCounts = [];
        foreach ($document->keywordResults as $result) {
            $keyword = $result->keyword?->keyword;
            if ($keyword) {
                $documentKeywordCounts[$keyword] = (int) $result->frequency_count;
            }
        }

        $keywordMatches = [];
        foreach ($proposalKeywordCounts as $keyword => $count) {
            if ((int) $count > 0 && ($documentKeywordCounts[$keyword] ?? 0) > 0) {
                $keywordMatches[] = $keyword;
            }
        }

        $keywordScore = count($proposalKeywordCounts) > 0
            ? count($keywordMatches) / max(1, count($proposalKeywordCounts))
            : 0;

        $score = (int) round(min(100, ($weightedOverlap * 50) + ($termOverlap * 30) + ($keywordScore * 20)));

        return [
            'score' => $score,
            'verdict' => $score >= 60 ? 'Strong alignment' : ($score >= 40 ? 'Partial alignment' : 'Low alignment'),
            'shared_words' => array_slice($shared, 0, 12),
            'keyword_matches' => $keywordMatches,
        ];
    }

    /**
     * @param array<string, mixed> $bestDocument
     * @return array{verdict:string,suitable:bool,score:int,confidence:int,message:string}
     */
    private function proposalSummary(int $bestScore, int $topAverage, array $bestDocument): array
    {
        if ($bestScore >= 60) {
            return [
                'verdict' => 'Suitable research direction',
                'suitable' => true,
                'score' => $bestScore,
                'confidence' => $topAverage,
                'message' => 'Your proposal shares strong terminology and keyword overlap with ' . $bestDocument['filename'] . '.',
            ];
        }

        if ($bestScore >= 40) {
            return [
                'verdict' => 'Promising, but review scope',
                'suitable' => true,
                'score' => $bestScore,
                'confidence' => $topAverage,
                'message' => 'Your proposal has useful overlap, but the topic focus should be reviewed against the closest papers.',
            ];
        }

        return [
            'verdict' => 'Low suitability',
            'suitable' => false,
            'score' => $bestScore,
            'confidence' => $topAverage,
            'message' => 'The proposal does not strongly match the analyzed papers based on top-word and keyword usage.',
        ];
    }
}
