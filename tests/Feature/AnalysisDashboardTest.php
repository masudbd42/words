<?php

namespace Tests\Feature;

use App\Models\AnalysisBatch;
use App\Models\Document;
use App\Models\DocumentKeywordResult;
use App\Models\Keyword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_page_displays_document_metadata_and_progress_state(): void
    {
        $batch = AnalysisBatch::create([
            'status' => AnalysisBatch::STATUS_PROCESSING,
            'total_documents' => 2,
            'word_limit' => 20,
        ]);

        $keyword = Keyword::create([
            'analysis_batch_id' => $batch->id,
            'keyword' => 'revenue',
        ]);

        $document = Document::create([
            'analysis_batch_id' => $batch->id,
            'original_filename' => 'annual-report.pdf',
            'stored_path' => 'analysis/1/annual-report.pdf',
            'file_size_bytes' => 1048576,
            'mime_type' => 'application/pdf',
            'checksum_sha256' => str_repeat('a', 64),
            'page_count' => 24,
            'status' => Document::STATUS_COMPLETED,
            'metadata' => [
                'pdf' => [
                    'page_count' => 24,
                    'details' => [
                        'Title' => 'Annual Report',
                        'Author' => 'Finance Team',
                        'Subject' => 'Annual financial review',
                        'Keywords' => 'finance, revenue, growth',
                        'Producer' => 'PDF Generator',
                    ],
                ],
                'analysis' => [
                    'word_count' => 321,
                    'unique_word_count' => 120,
                    'keyword_match_count' => 4,
                    'character_count' => 2048,
                    'intelligence_source' => 'local',
                ],
            ],
            'top_words' => ['revenue' => 8],
            'analyzed_at' => now(),
        ]);

        DocumentKeywordResult::create([
            'document_id' => $document->id,
            'keyword_id' => $keyword->id,
            'frequency_count' => 4,
        ]);

        $this->get(route('analysis.show', $batch))
            ->assertOk()
            ->assertSee('Word Intelligence Dashboard')
            ->assertSee('Document Metadata')
            ->assertSee('Annual Report')
            ->assertSee('Annual financial review')
            ->assertSee('24 pages')
            ->assertSee('1.0 MB');
    }

    public function test_progress_endpoint_returns_metadata_and_counts(): void
    {
        $batch = AnalysisBatch::create([
            'status' => AnalysisBatch::STATUS_PROCESSING,
            'total_documents' => 2,
            'word_limit' => 20,
        ]);

        Keyword::create([
            'analysis_batch_id' => $batch->id,
            'keyword' => 'margin',
        ]);

        Document::create([
            'analysis_batch_id' => $batch->id,
            'original_filename' => 'one.pdf',
            'stored_path' => 'analysis/1/one.pdf',
            'file_size_bytes' => 2048,
            'mime_type' => 'application/pdf',
            'checksum_sha256' => str_repeat('b', 64),
            'page_count' => 3,
            'status' => Document::STATUS_COMPLETED,
            'metadata' => [
                'pdf' => [
                    'page_count' => 3,
                    'details' => ['Title' => 'One', 'Subject' => 'One subject'],
                ],
                'analysis' => [
                    'word_count' => 40,
                    'unique_word_count' => 20,
                    'keyword_match_count' => 5,
                    'character_count' => 500,
                    'intelligence_source' => 'gemini',
                ],
            ],
            'top_words' => ['margin' => 5],
            'analyzed_at' => now(),
        ]);

        Document::create([
            'analysis_batch_id' => $batch->id,
            'original_filename' => 'two.pdf',
            'stored_path' => 'analysis/1/two.pdf',
            'file_size_bytes' => 4096,
            'mime_type' => 'application/pdf',
            'checksum_sha256' => str_repeat('c', 64),
            'page_count' => 7,
            'status' => Document::STATUS_FAILED,
            'error_message' => 'Parsing failed',
            'metadata' => [
                'pdf' => [
                    'page_count' => 7,
                    'details' => ['Title' => 'Two', 'Keywords' => 'failure, retry'],
                ],
                'analysis' => [
                    'word_count' => 60,
                    'unique_word_count' => 30,
                    'keyword_match_count' => 0,
                    'character_count' => 700,
                    'intelligence_source' => 'local',
                ],
            ],
            'analyzed_at' => now(),
        ]);

        $response = $this->getJson(route('analysis.progress', $batch));

        $response->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('processed', 2)
            ->assertJsonPath('completed', 1)
            ->assertJsonPath('failed', 1)
            ->assertJsonPath('documents.0.page_count', 3)
            ->assertJsonPath('documents.0.metadata.pdf.details.Title', 'One');
    }
}