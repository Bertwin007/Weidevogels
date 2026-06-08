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
        $storyLine = trim((string) ($attributes['story_line'] ?? ''));
        $caption = isset($attributes['caption']) ? trim((string) $attributes['caption']) : null;
        $species = trim((string) ($attributes['species'] ?? ''));
        $isPublishable = (bool) ($attributes['is_publishable'] ?? true);

        if (Schema::hasColumn('annotations', 'count')) {
            $attributes['count'] = self::parseCount($attributes['count_label'] ?? '1');
        }

        if (Schema::hasColumn('annotations', 'title') && empty($attributes['title'])) {
            $attributes['title'] = Str::limit($storyLine, 120, '') ?: Str::limit($species, 120, 'Moment');
        }

        if (Schema::hasColumn('annotations', 'story') && empty($attributes['story'])) {
            $attributes['story'] = ($caption !== null && $caption !== '') ? $caption : $storyLine;
        }

        if (Schema::hasColumn('annotations', 'publishable') && ! array_key_exists('publishable', $attributes)) {
            $attributes['publishable'] = $isPublishable;
        }

        if (Schema::hasColumn('annotations', 'youth_line') && empty($attributes['youth_line'])) {
            $attributes['youth_line'] = $storyLine;
        }

        if (Schema::hasColumn('annotations', 'youth_story_line') && $storyLine !== '') {
            $attributes['youth_story_line'] = $storyLine;
        }

        if (Schema::hasColumn('annotations', 'public_story') && $storyLine !== '') {
            $attributes['public_story'] = $storyLine;
        }

        if (Schema::hasColumn('annotations', 'annotated_at') && empty($attributes['annotated_at'])) {
            $attributes['annotated_at'] = now();
        }

        if (Schema::hasColumn('annotations', 'quality_score') && ! array_key_exists('quality_score', $attributes)) {
            $attributes['quality_score'] = 3;
        }

        if (Schema::hasColumn('annotations', 'is_highlight') && ! array_key_exists('is_highlight', $attributes)) {
            $attributes['is_highlight'] = false;
        }

        if (Schema::hasColumn('annotations', 'sponsor_hook') && empty($attributes['sponsor_hook'])) {
            $attributes['sponsor_hook'] = '';
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
    public static function donationClickAttributes(array $attributes, ?Observation $observation = null): array
    {
        if ($observation !== null && ! array_key_exists('observation_id', $attributes)) {
            $attributes['observation_id'] = $observation->id;
        }

        if (
            $observation !== null
            && Schema::hasColumn('donation_clicks', 'project_id')
            && ! array_key_exists('project_id', $attributes)
        ) {
            $attributes['project_id'] = $observation->project_id;
        }

        $hash = $attributes['ip_hash'] ?? null;
        $ipAddress = $attributes['ip_address'] ?? null;
        $userAgent = $attributes['user_agent'] ?? null;

        unset($attributes['ip_hash'], $attributes['ip_address'], $attributes['user_agent']);

        if ($hash !== null && Schema::hasColumn('donation_clicks', 'ip_hash')) {
            $attributes['ip_hash'] = $hash;
        }

        if ($ipAddress !== null && Schema::hasColumn('donation_clicks', 'ip_address')) {
            $attributes['ip_address'] = $ipAddress;
        }

        if ($userAgent !== null && Schema::hasColumn('donation_clicks', 'user_agent')) {
            $attributes['user_agent'] = $userAgent;
        }

        if (Schema::hasColumn('donation_clicks', 'clicked_at') && empty($attributes['clicked_at'])) {
            $attributes['clicked_at'] = now();
        }

        if (Schema::hasColumn('donation_clicks', 'source') && empty($attributes['source'])) {
            $attributes['source'] = $observation ? 'moment' : 'general';
        }

        return self::filterColumns('donation_clicks', $attributes);
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
