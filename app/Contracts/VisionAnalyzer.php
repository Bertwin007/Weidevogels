<?php

namespace App\Contracts;

use App\Data\AiAnnotationSuggestion;

interface VisionAnalyzer
{
    public function analyze(string $absoluteImagePath, ?string $contributorNote = null): AiAnnotationSuggestion;

    public function isConfigured(): bool;
}
