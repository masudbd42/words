<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Extract key themes and high-frequency terms using AI.
     *
     * @return array<string, int>
     */
    public function extractIntelligence(string $text, int $limit = 20): array
    {
        if (!$this->apiKey) {
            return [];
        }

        try {
            // Trim text to avoid payload limits
            $sampleText = mb_substr($text, 0, 15000);

            $prompt = "Analyze the following text and extract the top {$limit} most important words or short phrases (themes). 
                      Return ONLY a JSON object where keys are the words/phrases and values are their estimated importance or frequency (integers).
                      Exclude common stop words. 
                      Text: {$sampleText}";

            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $jsonString = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                $decoded = json_decode($jsonString, true);

                if (!is_array($decoded)) {
                    return [];
                }

                $normalized = [];
                foreach ($decoded as $word => $value) {
                    $word = trim((string) $word);
                    if ($word === '') {
                        continue;
                    }

                    $normalized[$word] = max(0, (int) $value);
                }

                arsort($normalized);

                return array_slice($normalized, 0, $limit, true);
            }

            Log::error('Gemini API Error', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception', ['error' => $e->getMessage()]);
        }

        return [];
    }
}
