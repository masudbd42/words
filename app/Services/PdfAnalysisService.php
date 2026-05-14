<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class PdfAnalysisService
{
    /**
     * Extract raw text from a PDF file.
     */
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function extractText(string $path): string
    {
        if (!file_exists($path)) {
            throw new \Exception("File not found: " . basename($path));
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            
            // Explicitly cleanup
            $pdf = null;
            $parser = null;
            
            return $text;
        } catch (\Exception $e) {
            throw new \Exception("PDF parsing failed: " . $e->getMessage());
        } finally {
            gc_collect_cycles();
        }
    }

    /**
     * Analyze a PDF once and return text, keyword counts, top words, and metadata.
     *
     * @param array<int, string> $keywords
     * @return array{text:string,keyword_counts:array<string, int>,top_words:array<string, int>,intelligence_source:string,metadata:array<string, mixed>}
     */
    public function analyzeDocument(string $path, array $keywords, int $wordLimit = 20): array
    {
        if (!file_exists($path)) {
            throw new \Exception('File not found: ' . basename($path));
        }

        try {
            $requestedWordLimit = max(1, min(100, $wordLimit));
            $storedWordLimit = max($requestedWordLimit, 100);
            $parser = new Parser();
            $pdf = $parser->parseFile($path);

            $text = $pdf->getText();
            $counts = $this->countKeywords($text, $keywords);
            $intelligence = $this->generateWordIntelligence($text, $storedWordLimit);
            $sourceSizeBytes = filesize($path) ?: null;
            $sourceChecksum = hash_file('sha256', $path) ?: null;

            $pages = [];
            try {
                $pages = $pdf->getPages();
            } catch (\Throwable $throwable) {
                $pages = [];
            }

            $details = $this->normalizeMetadataValue($pdf->getDetails());
            $selectedDetails = $this->selectPdfMetadata($details);
            $wordCount = $this->countWords($text);
            $uniqueWordCount = $this->countUniqueWords($text);
            $keywordMatchCount = array_sum($counts);
            $metadata = [
                'pdf' => [
                    'page_count' => count($pages),
                    'details' => $selectedDetails,
                    'raw_details' => $details,
                ],
                'analysis' => [
                    'character_count' => mb_strlen($text),
                    'word_count' => $wordCount,
                    'unique_word_count' => $uniqueWordCount,
                    'keyword_match_count' => $keywordMatchCount,
                    'intelligence_source' => $intelligence['source'],
                    'selected_word_limit' => $requestedWordLimit,
                    'available_word_limit' => count($intelligence['words']),
                    'source_checksum_sha256' => $sourceChecksum,
                    'source_size_bytes' => $sourceSizeBytes,
                ],
            ];

            $pdf = null;
            $parser = null;

            return [
                'text' => $text,
                'keyword_counts' => $counts,
                'top_words' => $intelligence['words'],
                'intelligence_source' => $intelligence['source'],
                'metadata' => $metadata,
            ];
        } catch (\Exception $exception) {
            throw new \Exception('PDF analysis failed: ' . $exception->getMessage());
        } finally {
            gc_collect_cycles();
        }
    }

    /**
     * Count keyword occurrences using strict word boundaries.
     *
     * @param array<int, string> $keywords
     * @return array<string, int>
     */
    public function countKeywords(string $text, array $keywords): array
    {
        $counts = [];

        foreach ($keywords as $keyword) {
            $pattern = $this->buildPattern($keyword);

            if ($pattern === null) {
                $counts[$keyword] = 0;
                continue;
            }

            $counts[$keyword] = preg_match_all($pattern, $text) ?: 0;
        }

        return $counts;
    }

    /**
     * Analyze a PDF file and return counts for each keyword.
     *
     * @param array<int, string> $keywords
     * @return array<string, int>
     */
    public function analyze(string $path, array $keywords): array
    {
        $text = $this->extractText($path);
        $counts = $this->countKeywords($text, $keywords);

        // Aggressive memory release
        $text = ''; 
        unset($text);
        gc_collect_cycles();

        return $counts;
    }

    public function extractTopWords(string $text, int $limit = 20): array
    {
        return $this->generateWordIntelligence($text, $limit)['words'];
    }

    /**
     * Generate word intelligence using Gemini first and a deterministic fallback second.
     *
     * @return array{words:array<string, int>,source:string}
     */
    public function generateWordIntelligence(string $text, int $limit = 20): array
    {
        $aiWords = $this->normalizeWordMap($this->geminiService->extractIntelligence($text, $limit), $limit);
        if (!empty($aiWords)) {
            return [
                'words' => $aiWords,
                'source' => 'gemini',
            ];
        }

        \Illuminate\Support\Facades\Log::debug('Gemini fallback used for Word Intelligence');

        $fallbackWords = $this->localWordIntelligence($text, $limit);

        return [
            'words' => $fallbackWords,
            'source' => 'local',
        ];
    }

    private function localWordIntelligence(string $text, int $limit = 20): array
    {
        if (empty(trim($text))) {
            return [];
        }

        $stopWords = ['the', 'and', 'a', 'to', 'of', 'in', 'is', 'it', 'for', 'on', 'with', 'as', 'at', 'by', 'an', 'be', 'this', 'that', 'which', 'from', 'are', 'was', 'were', 'or', 'not', 'but', 'if', 'has', 'have', 'had', 'been', 'can', 'will', 'would', 'should', 'could', 'about', 'their', 'there', 'they', 'what', 'when', 'where', 'who', 'how', 'than', 'then', 'them', 'these', 'those'];

        $clean = preg_replace('/[^a-z0-9\\s]/i', ' ', $text);
        $words = preg_split('/\\s+/', strtolower($clean), -1, PREG_SPLIT_NO_EMPTY);

        if (! $words) {
            return [];
        }

        $frequencies = array_count_values($words);

        $filtered = array_filter($frequencies, function ($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords, true) && !is_numeric($word);
        }, ARRAY_FILTER_USE_KEY);

        arsort($filtered);

        return array_slice($filtered, 0, $limit, true);
    }

    private function countWords(string $text): int
    {
        if (trim($text) === '') {
            return 0;
        }

        preg_match_all('/[\pL\pN]+(?:[\'\-][\pL\pN]+)*/u', $text, $matches);

        return count($matches[0] ?? []);
    }

    private function countUniqueWords(string $text): int
    {
        if (trim($text) === '') {
            return 0;
        }

        preg_match_all('/[\pL\pN]+(?:[\'\-][\pL\pN]+)*/u', strtolower($text), $matches);

        return count(array_unique($matches[0] ?? []));
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function normalizeMetadataValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeMetadataValue($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            return (array) $value;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $words
     * @return array<string, int>
     */
    private function normalizeWordMap(array $words, int $limit = 20): array
    {
        $normalized = [];

        foreach ($words as $word => $count) {
            $word = trim((string) $word);

            if ($word === '') {
                continue;
            }

            $normalized[$word] = max(0, (int) $count);
        }

        arsort($normalized);

        return array_slice($normalized, 0, max(1, $limit), true);
    }

    /**
     * @param array<string, mixed> $details
     * @return array<string, mixed>
     */
    private function selectPdfMetadata(array $details): array
    {
        $wantedKeys = ['Title', 'Author', 'Subject', 'Keywords', 'Creator', 'Producer', 'CreationDate', 'ModDate'];
        $selected = [];

        foreach ($wantedKeys as $wantedKey) {
            $selected[$wantedKey] = $this->findMetadataValueCaseInsensitive($details, $wantedKey);
        }

        return array_filter($selected, static fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param array<string, mixed> $details
     */
    private function findMetadataValueCaseInsensitive(array $details, string $needle): mixed
    {
        foreach ($details as $key => $value) {
            if (strcasecmp((string) $key, $needle) === 0) {
                return $value;
            }
        }

        return null;
    }

    private function buildPattern(string $keyword): ?string
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return null;
        }

        return '/\\b' . preg_quote($keyword, '/') . '\\b/iu';
    }
}
