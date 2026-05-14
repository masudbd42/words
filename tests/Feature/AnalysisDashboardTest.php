<?php

namespace Tests\Feature;

use App\Models\AnalysisBatch;
use App\Models\Document;
use App\Models\DocumentKeywordResult;
use App\Models\Keyword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_document_top_words_endpoint_returns_requested_limit(): void
    {
        $batch = AnalysisBatch::create([
            'status' => AnalysisBatch::STATUS_COMPLETED,
            'total_documents' => 1,
            'word_limit' => 50,
        ]);

        $document = Document::create([
            'analysis_batch_id' => $batch->id,
            'original_filename' => 'research-paper.pdf',
            'stored_path' => 'analysis/1/research-paper.pdf',
            'status' => Document::STATUS_COMPLETED,
            'metadata' => [
                'analysis' => [
                    'word_count' => 500,
                    'unique_word_count' => 120,
                ],
            ],
            'top_words' => [
                'machine learning' => 14,
                'climate' => 10,
                'policy' => 8,
            ],
        ]);

        $response = $this->getJson(route('analysis.documents.top-words', [$batch, $document]) . '?limit=2');

        $response->assertOk()
            ->assertJsonPath('document.filename', 'research-paper.pdf')
            ->assertJsonPath('limit', 2)
            ->assertJsonPath('available', 3);

        $this->assertSame([
            'machine learning' => 14,
            'climate' => 10,
        ], $response->json('top_words'));
    }

    public function test_proposal_comparison_returns_suitability_feedback(): void
    {
        $batch = AnalysisBatch::create([
            'status' => AnalysisBatch::STATUS_COMPLETED,
            'total_documents' => 1,
            'word_limit' => 50,
        ]);

        $machineLearning = Keyword::create([
            'analysis_batch_id' => $batch->id,
            'keyword' => 'machine learning',
        ]);

        $climate = Keyword::create([
            'analysis_batch_id' => $batch->id,
            'keyword' => 'climate',
        ]);

        $document = Document::create([
            'analysis_batch_id' => $batch->id,
            'original_filename' => 'ai-climate-paper.pdf',
            'stored_path' => 'analysis/1/ai-climate-paper.pdf',
            'status' => Document::STATUS_COMPLETED,
            'page_count' => 12,
            'metadata' => [
                'analysis' => [
                    'word_count' => 900,
                    'unique_word_count' => 240,
                ],
            ],
            'top_words' => [
                'machine' => 18,
                'learning' => 15,
                'climate' => 12,
                'policy' => 8,
            ],
        ]);

        DocumentKeywordResult::create([
            'document_id' => $document->id,
            'keyword_id' => $machineLearning->id,
            'frequency_count' => 4,
        ]);
        DocumentKeywordResult::create([
            'document_id' => $document->id,
            'keyword_id' => $climate->id,
            'frequency_count' => 6,
        ]);

        $proposal = UploadedFile::fake()->createWithContent(
            'proposal.txt',
            'This proposal studies machine learning for climate policy. Machine learning models support climate adaptation policy research.'
        );

        $response = $this->post(route('analysis.compare-proposal', $batch), [
            'proposal' => $proposal,
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('summary.suitable', true)
            ->assertJsonPath('documents.0.filename', 'ai-climate-paper.pdf');

        $this->assertGreaterThanOrEqual(40, $response->json('summary.score'));
        $this->assertNotEmpty($response->json('proposal_top_words'));
    }
}