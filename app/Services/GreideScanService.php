<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GreideScanService
{
    /**
     * @return array{species: list<array{nl: string, fy: string, count: int, confidence: int}>, live: bool, notes: ?string}
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
     * @return array{species: list<array{nl: string, fy: string, count: int, confidence: int}>, live: bool, notes: ?string}
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
        $species = $this->parseSpeciesJson($text);

        if ($species === []) {
            return $this->demoResult('Geen weidevogels herkend in de foto.');
        }

        return [
            'species' => $species,
            'live' => true,
            'notes' => null,
        ];
    }

    /**
     * @return list<array{nl: string, fy: string, count: int, confidence: int}>
     */
    private function parseSpeciesJson(string $content): array
    {
        $content = trim($content);
        $content = preg_replace('/^```json\s*|\s*```$/', '', $content) ?? $content;

        $json = json_decode($content, true);

        if (! is_array($json)) {
            return [];
        }

        $rows = $json['species'] ?? [];

        if (! is_array($rows)) {
            return [];
        }

        $species = [];

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

        return $species;
    }

    private function scanPrompt(): string
    {
        return <<<'PROMPT'
Je bent een herkenningsmodel voor Nederlandse weidevogels op Fries greideland.
Welke weidevogels zie je in deze foto? Denk aan: grutto, kievit, scholekster, tureluur, wulp, veldleeuwerik, graspieper, kemphaan, gele kwikstaart, zwarte stilt.

Antwoord UITSLUITEND met geldig JSON:
{"species":[{"nl":"Nederlandse naam","fy":"Friese naam indien bekend","count":1,"confidence":0.92}]}

Regels:
- confidence tussen 0 en 1 (bijv. 0.88)
- count = zichtbare vogels van die soort
- Lege lijst alleen als er echt geen weidevogels zichtbaar zijn
- Geen markdown, geen uitleg
PROMPT;
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
     * @return array{species: list<array{nl: string, fy: string, count: int, confidence: int}>, live: bool, notes: ?string}
     */
    private function demoResult(?string $notes = null): array
    {
        return [
            'species' => [
                ['nl' => 'Grutto', 'fy' => 'Skries', 'count' => 3, 'confidence' => 96],
                ['nl' => 'Kievit', 'fy' => 'Ljip', 'count' => 5, 'confidence' => 93],
                ['nl' => 'Tureluur', 'fy' => 'Tsjirk', 'count' => 2, 'confidence' => 88],
                ['nl' => 'Scholekster', 'fy' => 'Bonte wile', 'count' => 1, 'confidence' => 84],
            ],
            'live' => false,
            'notes' => $notes,
        ];
    }
}
