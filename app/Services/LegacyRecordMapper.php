<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LegacyRecordMapper
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function observationAttributes(array $attributes, ?UploadedFile $file = null): array
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

        if (Schema::hasColumn('observations', 'contributor_type') && ! isset($attributes['contributor_type'])) {
            $attributes['contributor_type'] = 'guest';
        }

        if ($file) {
            if (Schema::hasColumn('observations', 'original_filename')) {
                $attributes['original_filename'] = $file->getClientOriginalName();
            }
            if (Schema::hasColumn('observations', 'mime_type')) {
                $attributes['mime_type'] = $file->getMimeType() ?: 'image/jpeg';
            }
            if (Schema::hasColumn('observations', 'file_size')) {
                $attributes['file_size'] = $file->getSize();
            }
        }

        if (Schema::hasColumn('observations', 'uuid') && empty($attributes['uuid'])) {
            $attributes['uuid'] = (string) Str::uuid();
        }

        if (Schema::hasColumn('observations', 'source') && empty($attributes['source'])) {
            $attributes['source'] = 'web';
        }

        return self::filterColumns('observations', $attributes);
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

        if (! empty($attributes['annotator_id']) && Schema::hasColumn('annotations', 'user_id')) {
            $attributes['user_id'] = $attributes['annotator_id'];
        }

        if (
            ! Schema::hasColumn('annotations', 'annotator_id')
            && Schema::hasColumn('annotations', 'user_id')
        ) {
            unset($attributes['annotator_id']);
        }

        return self::filterColumns('annotations', $attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private static function filterColumns(string $table, array $attributes): array
    {
        $columns = Schema::getColumnListing($table);

        return array_intersect_key($attributes, array_flip($columns));
    }

    public static function parseCount(string $value): int
    {
        if (preg_match('/\d+/', $value, $matches) === 1) {
            return max(1, (int) $matches[0]);
        }

        return 1;
    }
}
