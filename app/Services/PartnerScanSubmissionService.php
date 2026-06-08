<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerScanSubmissionService
{
    /**
     * @param  list<array{nl: string, fy?: string, count?: int, confidence?: int}>  $species
     */
    public function submit(
        string $base64,
        string $mime,
        string $companyName,
        ?string $companyEmail,
        array $species,
        bool $live,
        ?string $storyLine = null,
        ?string $behavior = null,
        ?string $season = null,
    ): Observation {
        $project = Project::findLjippelan();

        if (! $project) {
            throw new \RuntimeException('Project Ljippelân is niet ingesteld.');
        }

        $binary = base64_decode($base64, true);

        if ($binary === false || $binary === '') {
            throw new \InvalidArgumentException('Ongeldige afbeelding.');
        }

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $path = 'observations/'.Str::random(40).'.'.$extension;

        if (! Storage::disk('public')->put($path, $binary)) {
            throw new \RuntimeException('Foto opslaan mislukt.');
        }

        $ai = $this->mapSpeciesToAiFields($species, $live, $storyLine, $behavior, $season);

        $attributes = LegacyRecordMapper::observationAttributes([
            'guest_name' => $companyName,
            'guest_email' => $companyEmail,
            'photo_path' => $path,
            'contributor_type' => 'business',
            'contributor_note' => 'B2B Greide-scan via /ondernemers',
            'status' => 'pending',
            'source' => 'partner_scan',
            'mime_type' => $mime,
            'file_size' => strlen($binary),
            'ai_species' => $ai['species'],
            'ai_count' => $ai['count'],
            'ai_behavior' => $ai['behavior'],
            'ai_season' => $ai['season'],
            'ai_confidence' => $ai['confidence'],
            'ai_notes' => json_encode($ai['notes'], JSON_UNESCAPED_UNICODE),
        ]);

        $attributes = $this->filterAiColumns($attributes);

        try {
            return $project->observations()->create($attributes);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            Log::error('Partner-scan inzending mislukt: '.$e->getMessage(), ['exception' => $e]);

            throw $e;
        }
    }

    /**
     * @param  list<array{nl: string, fy?: string, count?: int, confidence?: int}>  $species
     * @return array{species: string, count: int, behavior: string, season: string, confidence: int, notes: array<string, mixed>}
     */
    private function mapSpeciesToAiFields(
        array $species,
        bool $live,
        ?string $storyLine = null,
        ?string $behavior = null,
        ?string $season = null,
    ): array {
        $normalized = collect($species)
            ->filter(fn (array $row) => filled($row['nl'] ?? null))
            ->map(fn (array $row) => [
                'nl' => trim((string) $row['nl']),
                'fy' => trim((string) ($row['fy'] ?? '')),
                'count' => max(1, (int) ($row['count'] ?? 1)),
                'confidence' => max(0, min(100, (int) ($row['confidence'] ?? 80))),
            ])
            ->values()
            ->all();

        if ($normalized === []) {
            throw new \InvalidArgumentException('Geen soorten om in te zenden.');
        }

        $speciesLabel = collect($normalized)
            ->pluck('nl')
            ->unique()
            ->implode(', ');

        if (mb_strlen($speciesLabel) > 120) {
            $speciesLabel = mb_substr($speciesLabel, 0, 117).'…';
        }

        $totalBirds = array_sum(array_column($normalized, 'count'));
        $avgConfidence = (int) round(collect($normalized)->avg('confidence'));

        $behaviorText = filled($behavior)
            ? $this->limitText($behavior, 160)
            : sprintf('Greide-scan: %d soort(en), %d vogel(s)', count($normalized), $totalBirds);

        $storyLineText = filled($storyLine)
            ? $this->limitText($storyLine, 200)
            : $this->limitText(
                'Een mooi moment op het Friese greideland: '.$speciesLabel.' laten zien dat het hier leeft.',
                200
            );

        return [
            'species' => $speciesLabel,
            'count' => $totalBirds,
            'behavior' => $behaviorText,
            'season' => filled($season) ? $this->limitText($season, 60) : $this->seasonFromMonth((int) now()->format('n')),
            'confidence' => $avgConfidence,
            'notes' => [
                'species' => $normalized,
                'story_line' => $storyLineText,
                'provider' => $live ? 'gemini' : 'demo',
                'live' => $live,
                'scan_type' => 'partner',
                'submitted_at' => now()->toIso8601String(),
            ],
        ];
    }

    private function limitText(string $value, int $max): string
    {
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

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function filterAiColumns(array $attributes): array
    {
        foreach (['ai_species', 'ai_count', 'ai_behavior', 'ai_season', 'ai_confidence', 'ai_notes'] as $column) {
            if (! Schema::hasColumn('observations', $column)) {
                unset($attributes[$column]);
            }
        }

        if (! Schema::hasColumn('observations', 'source')) {
            unset($attributes['source']);
        }

        return $attributes;
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
}
