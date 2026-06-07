<?php

namespace App\Services;

class ExifService
{
    public function takenAt(string $absolutePath): ?\DateTimeInterface
    {
        if (! function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data($absolutePath);

        if (! is_array($exif)) {
            return null;
        }

        $raw = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? null;

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $raw);

        return $date ?: null;
    }
}
