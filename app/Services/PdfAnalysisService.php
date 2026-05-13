<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class PdfAnalysisService
{
    /**
     * Extract raw text from a PDF file.
     */
    public function extractText(string $path): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        unset($pdf, $parser);
        gc_collect_cycles();

        return $text;
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

        unset($text);
        gc_collect_cycles();

        return $counts;
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
