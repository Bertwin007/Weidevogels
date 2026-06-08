<?php

namespace App\Services;

use RuntimeException;

class EsgReportPdfService
{
    public function isAvailable(): bool
    {
        return class_exists(\Barryvdh\DomPDF\Facade\Pdf::class);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function render(array $data): mixed
    {
        if (! $this->isAvailable()) {
            throw new RuntimeException(
                'PDF-bibliotheek ontbreekt. Voer op de server uit: composer install (of bash scripts/plesk-deploy.sh).'
            );
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.bewijs-rapport', [
            'data' => $data,
            'preview' => false,
        ])->setPaper('a4');
    }

    public function filename(string $partnerSlug, int $season): string
    {
        return sprintf('biodiversiteits-bewijs-%s-%d.pdf', $partnerSlug, $season);
    }
}
