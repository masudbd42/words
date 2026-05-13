<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnalysisRequest;
use App\Jobs\ProcessPdfAnalysisJob;
use App\Models\AnalysisBatch;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    public function index(): View
    {
        return view('analysis.index');
    }

    public function store(StoreAnalysisRequest $request): RedirectResponse
    {
        $keywords = $request->keywords();
        $documents = $request->file('documents');
        $documentIds = [];

        $analysisBatch = DB::transaction(function () use ($keywords, $documents, &$documentIds) {
            $batch = AnalysisBatch::create([
                'status' => AnalysisBatch::STATUS_PROCESSING,
                'total_documents' => count($documents),
            ]);

            foreach ($keywords as $keyword) {
                $batch->keywords()->create(['keyword' => $keyword]);
            }

            foreach ($documents as $document) {
                $storedPath = $document->store('analysis/' . $batch->id);

                $record = $batch->documents()->create([
                    'original_filename' => $document->getClientOriginalName(),
                    'stored_path' => $storedPath,
                    'status' => Document::STATUS_QUEUED,
                    'error_message' => null,
                ]);

                $documentIds[] = $record->id;
            }

            return $batch;
        });

        foreach ($documentIds as $documentId) {
            ProcessPdfAnalysisJob::dispatch($documentId);
        }

        return redirect()->route('analysis.show', $analysisBatch);
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

        $total = $analysisBatch->documents->count();
        $completed = $analysisBatch->documents->where('status', Document::STATUS_COMPLETED)->count();
        $failed = $analysisBatch->documents->where('status', Document::STATUS_FAILED)->count();
        $processed = $completed + $failed;

        return view('analysis.show', [
            'analysisBatch' => $analysisBatch,
            'results' => $results,
            'total' => $total,
            'processed' => $processed,
            'completed' => $completed,
            'failed' => $failed,
        ]);
    }

    public function progress(AnalysisBatch $analysisBatch): JsonResponse
    {
        $total = $analysisBatch->documents()->count();
        $completed = $analysisBatch->documents()
            ->where('status', Document::STATUS_COMPLETED)
            ->count();
        $failed = $analysisBatch->documents()
            ->where('status', Document::STATUS_FAILED)
            ->count();
        $processed = $completed + $failed;

        return response()->json([
            'processed' => $processed,
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'is_complete' => $total > 0 && $processed >= $total,
        ]);
    }
}
