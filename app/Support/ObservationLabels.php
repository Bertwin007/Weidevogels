<?php

namespace App\Support;

class ObservationLabels
{
    public static function status(?string $status): string
    {
        return match ($status) {
            'pending', 'pending_annotation', 'processing_ai' => 'Wacht op annotatie',
            'published', 'approved' => 'Gepubliceerd',
            'rejected', 'not_publishable' => 'Afgewezen',
            'unpublished' => 'Offline',
            default => $status ?? '—',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function editableStatuses(): array
    {
        return [
            'pending' => 'Wacht op annotatie',
            'published' => 'Gepubliceerd',
            'rejected' => 'Afgewezen',
            'unpublished' => 'Offline',
        ];
    }

    public static function normalizeStatus(string $status): string
    {
        return match ($status) {
            'pending_annotation', 'processing_ai' => 'pending',
            'approved' => 'published',
            'not_publishable' => 'rejected',
            default => $status,
        };
    }
}
