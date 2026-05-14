<?php

namespace App\Jobs;

use App\Models\AnalysisBatch;
use App\Models\Document;
use App\Models\DocumentKeywordResult;
use App\Services\PdfAnalysisService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPdfAnalysisJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $documentId)
    {
    }

    public function handle(PdfAnalysisService $analysisService): void
    {
        // Extreme limits for massive/complex PDFs
        set_time_limit(0);
        ini_set('memory_limit', '2048M'); 

        // Ensure DB connection is alive for long-running workers
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            DB::reconnect();
        }

        $document = Document::with('analysisBatch.keywords')->find($this->documentId);
        
        if (!$document) {
            return;
        }

        $document->update([
            'status' => Document::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        try {
            $keywords = $document->analysisBatch->keywords;
            $keywordValues = $keywords->pluck('keyword')->all();
            
            $path = Storage::disk('local')->path($document->stored_path);

            $analysis = $analysisService->analyzeDocument($path, $keywordValues, $document->analysisBatch->word_limit);
            $counts = $analysis['keyword_counts'];
            $topWords = $analysis['top_words'];
            $metadata = $analysis['metadata'];
            $metadata['analysis']['keyword_count'] = array_sum($counts);
            $metadata['analysis']['intelligence_source'] = $analysis['intelligence_source'];

            foreach ($keywords as $keyword) {
                DocumentKeywordResult::updateOrCreate(
                    [
                        'document_id' => $document->id,
                        'keyword_id' => $keyword->id,
                    ],
                    [
                        'frequency_count' => $counts[$keyword->keyword] ?? 0,
                    ]
                );
            }

            $document->update([
                'status' => Document::STATUS_COMPLETED,
                'top_words' => $topWords,
                'page_count' => data_get($metadata, 'pdf.page_count'),
                'metadata' => array_merge($document->metadata ?? [], $metadata),
                'analyzed_at' => now(),
            ]);
        } catch (Exception $exception) {
            Log::error('PDF analysis failed.', [
                'document_id' => $document->id,
                'error' => $exception->getMessage(),
            ]);

            $document->update([
                'status' => Document::STATUS_FAILED,
                'error_message' => substr($exception->getMessage(), 0, 250),
            ]);
        } finally {
            $this->updateBatchStatus($document);
            
            // Aggressive memory cleanup
            unset($document, $keywords, $keywordValues, $counts);
            gc_collect_cycles();
        }
    }

    private function updateBatchStatus(Document $document): void
    {
        $analysisBatch = $document->analysisBatch;
        
        if (!$analysisBatch) return;

        $hasPending = $analysisBatch->documents()
            ->whereIn('status', [Document::STATUS_QUEUED, Document::STATUS_PROCESSING])
            ->exists();

        if (! $hasPending) {
            $analysisBatch->aggregateIntelligence();
        }
    }
}
