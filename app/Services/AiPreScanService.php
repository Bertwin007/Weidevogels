<?php

namespace App\Services;

use App\Contracts\VisionAnalyzer;
use App\Data\AiAnnotationSuggestion;
use App\Models\Observation;
use App\Services\Vision\GeminiVisionAnalyzer;
use App\Services\Vision\HeuristicVisionAnalyzer;
use App\Services\Vision\OpenAiVisionAnalyzer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AiPreScanService
{
    public function analyze(Observation $observation): AiAnnotationSuggestion
    {
        if (! config('greidefugels.ai.enabled')) {
            return new AiAnnotationSuggestion(notes: 'AI-voorscan staat uit.');
        }

        $absolute = $observation->absolutePhotoPath();

        if ($absolute === null) {
            return new AiAnnotationSuggestion(notes: 'Geen fotobestand gevonden voor analyse.');
        }

        $analyzer = $this->resolveAnalyzer();
        $suggestion = $analyzer->analyze($absolute, $observation->contributor_note);

        $this->storeSuggestion($observation, $suggestion);

        return $suggestion;
    }

    public function isConfigured(): bool
    {
        return $this->usesRealVision();
    }

    public function usesRealVision(): bool
    {
        return match (config('greidefugels.ai.provider')) {
            'openai' => app(OpenAiVisionAnalyzer::class)->isConfigured(),
            'gemini' => app(GeminiVisionAnalyzer::class)->isConfigured(),
            default => false,
        };
    }

    public function activeProvider(): string
    {
        if ($this->usesRealVision()) {
            return (string) config('greidefugels.ai.provider', 'gemini');
        }

        return 'heuristic';
    }

    protected function resolveAnalyzer(): VisionAnalyzer
    {
        $provider = config('greidefugels.ai.provider', 'gemini');

        $analyzer = match ($provider) {
            'openai' => app(OpenAiVisionAnalyzer::class),
            'gemini' => app(GeminiVisionAnalyzer::class),
            'heuristic', 'none' => app(HeuristicVisionAnalyzer::class),
            default => app(HeuristicVisionAnalyzer::class),
        };

        if ($analyzer->isConfigured()) {
            return $analyzer;
        }

        if ($analyzer instanceof HeuristicVisionAnalyzer) {
            return $analyzer;
        }

        Log::warning('AI-vision provider niet geconfigureerd, gebruik basisvoorstel.', [
            'provider' => $provider,
        ]);

        return app(HeuristicVisionAnalyzer::class);
    }

    protected function storeSuggestion(Observation $observation, AiAnnotationSuggestion $suggestion): void
    {
        $attributes = [
            'status' => $observation->isPendingAnnotation() ? 'processing_ai' : $observation->statusValue(),
        ];

        if (Schema::hasColumn('observations', 'ai_species')) {
            $attributes['ai_species'] = $suggestion->species;
        }

        if (Schema::hasColumn('observations', 'ai_count') && $suggestion->countLabel !== null) {
            $attributes['ai_count'] = LegacyRecordMapper::parseCount($suggestion->countLabel);
        }

        if (Schema::hasColumn('observations', 'ai_behavior')) {
            $attributes['ai_behavior'] = $this->limitAiText($suggestion->behavior, 160);
        }

        if (Schema::hasColumn('observations', 'ai_season')) {
            $attributes['ai_season'] = $suggestion->season;
        }

        if (Schema::hasColumn('observations', 'ai_confidence')) {
            $attributes['ai_confidence'] = $suggestion->confidence;
        }

        if (Schema::hasColumn('observations', 'ai_notes')) {
            $attributes['ai_notes'] = json_encode(array_filter([
                'story_line' => $this->limitAiText($suggestion->storyLine, 200),
                'caption' => $suggestion->caption,
                'provider' => $suggestion->provider,
                'message' => $suggestion->notes,
                'analyzed_at' => now()->toIso8601String(),
            ]), JSON_UNESCAPED_UNICODE);
        }

        try {
            $observation->update($attributes);

            if ($observation->isPendingAnnotation() || $observation->statusValue() === 'processing_ai') {
                $observation->update(['status' => 'pending']);
            }
        } catch (\Throwable $e) {
            Log::error('AI-voorstel opslaan mislukt: '.$e->getMessage(), [
                'observation_id' => $observation->id,
                'exception' => $e,
            ]);
        }
    }

    private function limitAiText(?string $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (mb_strlen($value) <= $max) {
            return $value;
        }

        $truncated = mb_substr($value, 0, $max);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > (int) ($max * 0.6)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated, '.,;:!?…').'…';
    }
}
