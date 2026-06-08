<?php

namespace App\Data;

readonly class AiAnnotationSuggestion
{
    public function __construct(
        public ?string $species = null,
        public ?string $countLabel = null,
        public ?string $behavior = null,
        public ?string $season = null,
        public ?string $storyLine = null,
        public ?string $caption = null,
        public ?int $confidence = null,
        public ?string $provider = null,
        public ?string $notes = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'species' => $this->species,
            'count_label' => $this->countLabel,
            'behavior' => $this->behavior,
            'season' => $this->season,
            'story_line' => $this->storyLine,
            'caption' => $this->caption,
            'confidence' => $this->confidence,
            'provider' => $this->provider,
            'notes' => $this->notes,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function isEmpty(): bool
    {
        return $this->species === null
            && $this->storyLine === null
            && $this->behavior === null;
    }

    public function isHeuristicSuggestion(): bool
    {
        return ($this->provider ?? '') === 'heuristic';
    }
}
