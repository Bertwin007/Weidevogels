<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class LegacyRecordMapper
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function observationAttributes(array $attributes): array
    {
        if (($attributes['status'] ?? null) === 'pending_annotation') {
            $attributes['status'] = 'pending';
        }

        if (
            array_key_exists('photo_path', $attributes)
            && ! Schema::hasColumn('observations', 'photo_path')
            && Schema::hasColumn('observations', 'image_path')
        ) {
            $attributes['image_path'] = $attributes['photo_path'];
            unset($attributes['photo_path']);
        }

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function annotationAttributes(array $attributes): array
    {
        if (Schema::hasColumn('annotations', 'count')) {
            $attributes['count'] = self::parseCount($attributes['count_label'] ?? '1');
        }

        if (Schema::hasColumn('annotations', 'youth_story_line') && ! empty($attributes['story_line'])) {
            $attributes['youth_story_line'] = $attributes['story_line'];
        }

        if (Schema::hasColumn('annotations', 'public_story') && ! empty($attributes['story_line'])) {
            $attributes['public_story'] = $attributes['story_line'];
        }

        if (
            ! Schema::hasColumn('annotations', 'annotator_id')
            && Schema::hasColumn('annotations', 'user_id')
            && ! empty($attributes['annotator_id'])
        ) {
            $attributes['user_id'] = $attributes['annotator_id'];
            unset($attributes['annotator_id']);
        }

        return $attributes;
    }

    public static function parseCount(string $value): int
    {
        if (preg_match('/\d+/', $value, $matches) === 1) {
            return max(1, (int) $matches[0]);
        }

        return 1;
    }
}
