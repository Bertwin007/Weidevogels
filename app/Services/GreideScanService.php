<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GreideScanService
{
    /**
     * @return array{
     *     species: list<array{nl: string, fy: string, count: int, confidence: int}>,
     *     story_line: ?string,
     *     caption: ?string,
     *     behavior: ?string,
     *     season: ?string,
     *     live: bool,
     *     notes: ?string
     * }
     */
    public function scanBase64(string $base64, string $mime = 'image/jpeg'): array
    {
        $binary = base64_decode($base64, true);

        if ($binary === false || $binary === '') {
            return $this->demoResult('Ongeldige afbeelding.');
        }

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $tempPath = tempnam(sys_get_temp_dir(), 'greide-scan-');

        if ($tempPath === false) {
            return $this->demoResult('Tijdelijk bestand kon niet worden aangemaakt.');
        }

        $path = $tempPath.'.'.$extension;

        try {
            if (! rename($tempPath, $path)) {
                $path = $tempPath;
            }

            file_put_contents($path, $binary);

            return $this->scanFile($path, $mime);
        } finally {
            @unlink($path);
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * @return array{
     *     species: list<array{nl: string, fy: string, count: int, confidence: int}>,
     *     story_line: ?string,
     *     behavior: ?string,
     *     season: ?string,
     *     live: bool,
     *     notes: ?string
     * }
     */
    public function scanFile(string $absoluteImagePath, ?string $mime = null): array
    {
        if (! is_file($absoluteImagePath)) {
            return $this->demoResult('Bestand niet gevonden.');
        }

        $apiKey = config('greidefugels.ai.gemini.api_key');

        if (! filled($apiKey)) {
            return $this->demoResult('Geen AI-sleutel geconfigureerd.');
        }

        $mime ??= mime_content_type($absoluteImagePath) ?: 'image/jpeg';
        $model = config('greidefugels.ai.gemini.model');
        $imageData = $this->encodedImagePayload($absoluteImagePath, $mime);

        $response = Http::timeout(90)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [[
                    'parts' => [
                        ['text' => $this->scanPrompt()],
                        [
                            'inline_data' => [
                                'mime_type' => $imageData['mime'],
                                'data' => $imageData['data'],
                            ],
                        ],
                    ],
                ]],
                'generationConfig' => [
                    'temperature' => 0.15,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('Greide-scan mislukt', ['status' => $response->status(), 'body' => $response->body()]);

            return $this->demoResult('Google AI-analyse mislukt ('.$response->status().').');
        }

        $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
        $parsed = $this->parseScanJson($text);

        if ($parsed['species'] === []) {
            return $this->demoResult('Geen weidevogels herkend in de foto.');
        }

        return [
            'species' => $parsed['species'],
            'story_line' => $parsed['story_line'],
            'caption' => $parsed['caption'],
            'behavior' => $parsed['behavior'],
            'season' => $parsed['season'],
            'live' => true,
            'notes' => null,
        ];
    }

    /**
     * @return array{
     *     species: list<array{nl: string, fy: string, count: int, confidence: int}>,
     *     story_line: ?string,
     *     caption: ?string,
     *     behavior: ?string,
     *     season: ?string
     * }
     */
    private function parseScanJson(string $content): array
    {
        $content = trim($content);
        $content = preg_replace('/^```json\s*|\s*```$/', '', $content) ?? $content;

        $json = json_decode($content, true);

        if (! is_array($json)) {
            return ['species' => [], 'story_line' => null, 'caption' => null, 'behavior' => null, 'season' => null];
        }

        $rows = $json['species'] ?? [];
        $species = [];

        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $nl = trim((string) ($row['nl'] ?? ''));

                if ($nl === '') {
                    continue;
                }

                $confidence = 80;

                if (isset($row['confidence']) && is_numeric($row['confidence'])) {
                    $raw = (float) $row['confidence'];
                    $confidence = $raw <= 1 ? (int) round($raw * 100) : (int) round($raw);
                    $confidence = max(0, min(100, $confidence));
                }

                $species[] = [
                    'nl' => $nl,
                    'fy' => trim((string) ($row['fy'] ?? '')),
                    'count' => max(1, (int) ($row['count'] ?? 1)),
                    'confidence' => $confidence,
                ];
            }
        }

        $storyLine = $this->limitedText($json['story_line'] ?? $json['story'] ?? null, 200);
        $caption = $this->limitedText($json['caption'] ?? $json['toelichting'] ?? null, 2000);
        $behavior = $this->limitedText($json['behavior'] ?? null, 160);
        $season = $this->limitedText($json['season'] ?? null, 60);

        if ($storyLine === null && $species !== []) {
            $storyLine = $this->fallbackStoryLine($species);
        }

        if ($caption === null && $species !== []) {
            $caption = $this->fallbackCaption($species, $behavior, $season);
        }

        return [
            'species' => $species,
            'story_line' => $storyLine,
            'caption' => $caption,
            'behavior' => $behavior,
            'season' => $season,
        ];
    }

    private function scanPrompt(): string
    {
        return <<<'PROMPT'
Je bent een ervaren weidevogel-spotter voor Agrarisch Natuurfonds Fryslân op Fries greideland.
Welke weidevogels zie je? Denk aan: grutto, kievit, scholekster, tureluur, wulp, veldleeuwerik, graspieper, kemphaan, gele kwikstaart, zwarte stilt.

Antwoord UITSLUITEND met geldig JSON:
{
  "species":[{"nl":"Nederlandse naam","fy":"Friese naam","count":1,"confidence":0.92}],
  "behavior":"kort gedrag in het Nederlands, max 160 tekens",
  "season":"Lente, Zomer, Herfst of Winter",
  "story_line":"warm publiek verhaal in het Nederlands, STRIKT max 200 tekens inclusief spaties",
  "caption":"langere toelichting in het Nederlands, 300-800 tekens, beschrijf soorten, gedrag, omgeving en waarom dit biodiversiteitsbewijs is; max 2000 tekens"
}

Regels:
- confidence tussen 0 en 1
- story_line MOET ≤200 tekens zijn — tel je tekens, één korte zin
- caption is de uitgebreide toelichting (niet hetzelfde als story_line), max 2000 tekens
- behavior max 160 tekens
- Lege species-lijst alleen als er echt geen weidevogels zichtbaar zijn
- Geen markdown, geen uitleg
PROMPT;
    }

    /**
     * @param  list<array{nl: string, fy: string, count: int, confidence: int}>  $species
     */
    private function fallbackStoryLine(array $species): string
    {
        $lead = collect($species)->take(2)->pluck('nl')->implode(' en ');
        $line = "Een mooi moment op het Friese greideland: {$lead} laten zien dat het hier leeft.";

        return $this->limitedText($line, 200) ?? $line;
    }

    /**
     * @param  list<array{nl: string, fy: string, count: int, confidence: int}>  $species
     */
    private function fallbackCaption(array $species, ?string $behavior, ?string $season): string
    {
        $names = collect($species)->map(fn (array $row) => $row['nl'].($row['count'] > 1 ? " ({$row['count']}×)" : ''))->implode(', ');
        $seasonText = $season ? " in het {$season}" : '';
        $behaviorText = $behavior ? " Gedrag: {$behavior}." : '';

        $text = "Op deze foto uit het Friese greideland{$seasonText} zijn onder meer {$names} waargenomen.{$behaviorText} "
            .'Dit moment laat zien dat actief beheerd greideland een thuis blijft voor weidevogels — waardevol bewijs voor biodiversiteit en het werk van Agrarisch Natuurfonds Fryslân.';

        return $this->limitedText($text, 2000) ?? $text;
    }

    private function limitedText(mixed $value, int $max): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
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

    /**
     * @return array{
     *     species: list<array{nl: string, fy: string, count: int, confidence: int}>,
     *     story_line: ?string,
     *     behavior: ?string,
     *     season: ?string,
     *     live: bool,
     *     notes: ?string
     * }
     */
    private function demoResult(?string $notes = null): array
    {
        $species = [
            ['nl' => 'Grutto', 'fy' => 'Skries', 'count' => 3, 'confidence' => 96],
            ['nl' => 'Kievit', 'fy' => 'Ljip', 'count' => 5, 'confidence' => 93],
            ['nl' => 'Tureluur', 'fy' => 'Tsjirk', 'count' => 2, 'confidence' => 88],
            ['nl' => 'Scholekster', 'fy' => 'Bonte wile', 'count' => 1, 'confidence' => 84],
        ];

        $behavior = 'Waarneming op greideland met broedzorg en foerageren.';
        $season = $this->seasonFromMonth((int) date('n'));

        return [
            'species' => $species,
            'story_line' => $this->fallbackStoryLine($species),
            'caption' => $this->fallbackCaption($species, $behavior, $season),
            'behavior' => $behavior,
            'season' => $season,
            'live' => false,
            'notes' => $notes,
        ];
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
