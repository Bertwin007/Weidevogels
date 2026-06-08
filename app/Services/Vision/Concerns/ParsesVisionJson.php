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
            behavior: $this->stringOrNull($json['behavior'] ?? null),
            season: $this->stringOrNull($json['season'] ?? null),
            storyLine: $this->stringOrNull($json['story_line'] ?? $json['story'] ?? null),
            caption: $this->stringOrNull($json['caption'] ?? null),
            confidence: isset($json['confidence']) ? (int) $json['confidence'] : null,
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

    protected function visionPrompt(?string $contributorNote): string
    {
        $note = $contributorNote ? "Toelichting van de fotograaf: {$contributorNote}\n" : '';

        return <<<PROMPT
Je bent een vrijwilliger die weidevogelfoto's uit Fryslân (Nederland) beschrijft voor Agrarisch Natuurfonds Fryslân.
Analyseer de foto en geef ALLEEN geldig JSON terug met deze velden:
- species (Nederlandse soortnaam, bijv. Grutto, Kievit, Scholekster)
- count_label (korte tekst, bijv. "2" of "een paar")
- behavior (kort gedrag in het Nederlands)
- season (Lente, Zomer, Herfst of Winter — schat op basis van foto en datum)
- story_line (max 200 tekens, publieke verhaalregel in het Nederlands)
- caption (optionele langere toelichting in het Nederlands)
- confidence (0-100, hoe zeker je bent)

{$note}Antwoord uitsluitend met JSON, geen markdown.
PROMPT;
    }
}
