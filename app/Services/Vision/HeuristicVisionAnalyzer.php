<?php

namespace App\Services\Vision;

use App\Contracts\VisionAnalyzer;
use App\Data\AiAnnotationSuggestion;

class HeuristicVisionAnalyzer implements VisionAnalyzer
{
    public function isConfigured(): bool
    {
        return true;
    }

    public function analyze(string $absoluteImagePath, ?string $contributorNote = null): AiAnnotationSuggestion
    {
        $season = $this->guessSeasonFromPath($absoluteImagePath);
        $species = $this->guessSpeciesFromText($contributorNote);

        return new AiAnnotationSuggestion(
            species: $species,
            countLabel: '1',
            behavior: 'Waarneming op greideland',
            season: $season,
            storyLine: $species
                ? "Een {$species} in het Friese greideland."
                : 'Een moment op het Friese greideland.',
            caption: $contributorNote,
            confidence: 25,
            provider: 'heuristic',
            notes: 'Basisvoorstel zonder AI — controleer en vul aan.',
        );
    }

    private function guessSeasonFromPath(string $absoluteImagePath): string
    {
        if (! function_exists('exif_read_data')) {
            return $this->seasonFromMonth((int) date('n'));
        }

        $exif = @exif_read_data($absoluteImagePath);
        $raw = is_array($exif) ? ($exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? null) : null;

        if (is_string($raw)) {
            $date = \DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $raw);

            if ($date) {
                return $this->seasonFromMonth((int) $date->format('n'));
            }
        }

        return $this->seasonFromMonth((int) date('n'));
    }

    private function seasonFromMonth(int $month): string
    {
        return match (true) {
            $month >= 3 && $month <= 5 => 'Lente',
            $month >= 6 && $month <= 8 => 'Zomer',
            $month >= 9 && $month <= 11 => 'Herfst',
            default => 'Winter',
        };
    }

    private function guessSpeciesFromText(?string $text): ?string
    {
        if (! filled($text)) {
            return null;
        }

        $lower = mb_strtolower($text);

        foreach (['grutto', 'kievit', 'scholekster', 'tureluur', 'veldleeuwerik'] as $species) {
            if (str_contains($lower, $species)) {
                return ucfirst($species);
            }
        }

        return null;
    }
}
