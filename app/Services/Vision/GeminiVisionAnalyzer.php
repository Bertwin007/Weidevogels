<?php

namespace App\Services\Vision;

use App\Contracts\VisionAnalyzer;
use App\Data\AiAnnotationSuggestion;
use App\Services\Vision\Concerns\ParsesVisionJson;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiVisionAnalyzer implements VisionAnalyzer
{
    use ParsesVisionJson;

    public function isConfigured(): bool
    {
        return filled(config('greidefugels.ai.gemini.api_key'));
    }

    public function analyze(string $absoluteImagePath, ?string $contributorNote = null): AiAnnotationSuggestion
    {
        if (! $this->isConfigured()) {
            return new AiAnnotationSuggestion(provider: 'gemini', notes: 'Google AI API-sleutel ontbreekt.');
        }

        $mime = mime_content_type($absoluteImagePath) ?: 'image/jpeg';
        $model = config('greidefugels.ai.gemini.model');
        $apiKey = config('greidefugels.ai.gemini.api_key');

        $response = Http::timeout(60)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [
                        ['text' => $this->visionPrompt($contributorNote)],
                        [
                            'inline_data' => [
                                'mime_type' => $mime,
                                'data' => base64_encode((string) file_get_contents($absoluteImagePath)),
                            ],
                        ],
                    ],
                ]],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('Gemini vision mislukt', ['status' => $response->status(), 'body' => $response->body()]);

            return new AiAnnotationSuggestion(
                provider: 'gemini',
                notes: 'Google AI-analyse mislukt ('.$response->status().').',
            );
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        return $this->parseSuggestion((string) $text, 'gemini');
    }
}
