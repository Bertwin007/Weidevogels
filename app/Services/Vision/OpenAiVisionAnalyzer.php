<?php

namespace App\Services\Vision;

use App\Contracts\VisionAnalyzer;
use App\Data\AiAnnotationSuggestion;
use App\Services\Vision\Concerns\ParsesVisionJson;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiVisionAnalyzer implements VisionAnalyzer
{
    use ParsesVisionJson;

    public function isConfigured(): bool
    {
        return filled(config('greidefugels.ai.openai.api_key'));
    }

    public function analyze(string $absoluteImagePath, ?string $contributorNote = null): AiAnnotationSuggestion
    {
        if (! $this->isConfigured()) {
            return new AiAnnotationSuggestion(provider: 'openai', notes: 'OpenAI API-sleutel ontbreekt.');
        }

        $mime = mime_content_type($absoluteImagePath) ?: 'image/jpeg';
        $base64 = base64_encode((string) file_get_contents($absoluteImagePath));

        $response = Http::timeout(60)
            ->withToken(config('greidefugels.ai.openai.api_key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('greidefugels.ai.openai.model'),
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $this->visionPrompt($contributorNote)],
                        ['type' => 'image_url', 'image_url' => ['url' => "data:{$mime};base64,{$base64}"]],
                    ],
                ]],
            ]);

        if (! $response->successful()) {
            Log::warning('OpenAI vision mislukt', ['status' => $response->status(), 'body' => $response->body()]);

            return new AiAnnotationSuggestion(
                provider: 'openai',
                notes: 'OpenAI-analyse mislukt ('.$response->status().').',
            );
        }

        $text = data_get($response->json(), 'choices.0.message.content', '');

        return $this->parseSuggestion((string) $text, 'openai');
    }
}
