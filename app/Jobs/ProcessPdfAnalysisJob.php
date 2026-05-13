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
        $document = Document::with('analysisBatch.keywords')->findOrFail($this->documentId);

        $document->update([
            'status' => Document::STATUS_PROCESSING,
            'error_message' => null,
        ]);

        try {
            $keywords = $document->analysisBatch->keywords;
            $keywordValues = $keywords->pluck('keyword')->all();
            $path = Storage::disk('local')->path($document->stored_path);
            $counts = $analysisService->analyze($path, $keywordValues);

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
            ]);
            $this->updateBatchStatus($document);
        } catch (Exception $exception) {
            Log::error('PDF analysis failed.', [
                'document_id' => $document->id,
                'error' => $exception->getMessage(),
            ]);

            $document->update([
                'status' => Document::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
            ]);
            $this->updateBatchStatus($document);
        }
    }

    private function updateBatchStatus(Document $document): void
    {
        $analysisBatch = $document->analysisBatch;
        $hasPending = $analysisBatch->documents()
            ->whereIn('status', [Document::STATUS_QUEUED, Document::STATUS_PROCESSING])
            ->exists();

        if (! $hasPending) {
            $analysisBatch->update(['status' => AnalysisBatch::STATUS_COMPLETED]);
        }
    }
}
