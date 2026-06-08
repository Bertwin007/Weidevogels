<?php

namespace App\Services\Vision\Concerns;

use App\Data\AiAnnotationSuggestion;

trait ParsesVisionJson
{
    protected function parseSuggestion(string $content, string $provider): AiAnnotationSuggestion
    {
        $json = $this->extractJson($content);

        if ($json === null) {
            return new AiAnnotationSuggestion(provider: $provider, notes: 'Kon AI-antwoord niet verwerken.');
        }

        return new AiAnnotationSuggestion(
            species: $this->stringOrNull($json['species'] ?? null),
            countLabel: $this->stringOrNull($json['count_label'] ?? $json['count'] ?? null),
            behavior: $this->limitedString($json['behavior'] ?? null, 160),
            season: $this->stringOrNull($json['season'] ?? null),
            storyLine: $this->limitedString($json['story_line'] ?? $json['story'] ?? null, 200),
            caption: $this->stringOrNull($json['caption'] ?? null),
            confidence: isset($json['confidence']) ? max(0, min(100, (int) $json['confidence'])) : 80,
            provider: $provider,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function extractJson(string $content): ?array
    {
        $content = trim($content);

        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content) ?? $content;
            $content = preg_replace('/\s*```$/', '', $content) ?? $content;
        }

        $decoded = json_decode($content, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $content, $matches) === 1) {
            $decoded = json_decode($matches[0], true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    protected function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    protected function limitedString(mixed $value, int $max): ?string
    {
        $value = $this->stringOrNull($value);

        if ($value === null || mb_strlen($value) <= $max) {
            return $value;
        }

        $truncated = mb_substr($value, 0, $max);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > (int) ($max * 0.6)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated, '.,;:!?…').'…';
    }

    protected function visionPrompt(?string $contributorNote): string
    {
        $note = $contributorNote ? "Toelichting van de fotograaf: {$contributorNote}\n" : '';

        return <<<PROMPT
Je bent een ervaren weidevogel-spotter voor Agrarisch Natuurfonds Fryslân in Fryslân (Nederland).
Bekijk de foto nauwkeurig en herken weidevogels op greideland.

Veelvoorkomende soorten: Grutto, Kievit, Scholekster, Tureluur, Veldleeuwerik, Zwarte stilt, Kemphaan, Wulp.
Geef de meest waarschijnlijke soort in het Nederlands. Tel zichtbare vogels. Beschrijf concreet gedrag (balts, broeden, voeren, vliegend, rusten, kuiken).
Kies seizoen: Lente, Zomer, Herfst of Winter (foto + omgeving).

Geef ALLEEN geldig JSON met:
- species
- count_label
- behavior (max 160 tekens, kort gedrag in het Nederlands)
- season
- story_line (STRIKT maximaal 200 tekens inclusief spaties en leestekens; tel je tekens; één korte, warme zin voor het publiek; nooit langer dan 200)
- caption (optioneel, mag langer)
- confidence (0-100; gebruik 75-95 bij duidelijke herkenning, 55-74 bij twijfel, onder 55 alleen als foto onbruikbaar is)

Belangrijk: story_line MOET ≤200 tekens zijn. Controleer de lengte vóór je antwoordt.

{$note}Antwoord uitsluitend met JSON, geen markdown.
PROMPT;
    }
}
