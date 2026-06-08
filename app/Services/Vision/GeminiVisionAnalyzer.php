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

        $imageData = $this->encodedImagePayload($absoluteImagePath, $mime);

        $response = Http::timeout(90)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [[
                    'parts' => [
                        ['text' => $this->visionPrompt($contributorNote)],
                        [
                            'inline_data' => [
                                'mime_type' => $imageData['mime'],
                                'data' => $imageData['data'],
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
            $detail = data_get($response->json(), 'error.message', $response->body());
            Log::warning('Gemini vision mislukt', ['status' => $response->status(), 'body' => $response->body()]);

            return new AiAnnotationSuggestion(
                provider: 'gemini',
                notes: 'Google AI-analyse mislukt ('.$response->status().'): '.$this->truncate($detail, 200),
            );
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        return $this->parseSuggestion((string) $text, 'gemini');
    }

    /**
     * @return array{mime: string, data: string}
     */
    private function encodedImagePayload(string $absoluteImagePath, string $mime): array
    {
        $contents = (string) file_get_contents($absoluteImagePath);
        $maxBytes = 4 * 1024 * 1024;

        if (strlen($contents) <= $maxBytes || ! function_exists('imagecreatefromstring')) {
            return ['mime' => $mime, 'data' => base64_encode($contents)];
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            return ['mime' => $mime, 'data' => base64_encode($contents)];
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, 1920 / max($width, 1), 1920 / max($height, 1));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($resized, null, 85);
        $jpeg = (string) ob_get_clean();

        imagedestroy($image);
        imagedestroy($resized);

        return ['mime' => 'image/jpeg', 'data' => base64_encode($jpeg)];
    }

    private function truncate(string $value, int $max): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);

        return mb_strlen($value) > $max ? mb_substr($value, 0, $max).'…' : $value;
    }
}
