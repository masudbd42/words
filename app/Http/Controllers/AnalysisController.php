<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnalysisRequest;
use App\Jobs\ProcessPdfAnalysisJob;
use App\Models\AnalysisBatch;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
                'word_limit' => (int) $request->input('word_limit', 20),
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
            'top_words' => $analysisBatch->top_words,
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
}
