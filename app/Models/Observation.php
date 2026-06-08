<?php

namespace App\Models;

use App\Enums\ObservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Observation extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'guest_name',
        'guest_email',
        'contributor_type',
        'photo_path',
        'image_path',
        'original_filename',
        'mime_type',
        'file_size',
        'source',
        'uuid',
        'contributor_note',
        'exif_taken_at',
        'status',
        'slug',
        'published_at',
        'ai_species',
        'ai_count',
        'ai_behavior',
        'ai_season',
        'ai_confidence',
        'ai_notes',
    ];

    protected function casts(): array
    {
        return [
            'exif_taken_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function statusValue(): string
    {
        return (string) ($this->attributes['status'] ?? ObservationStatus::PendingAnnotation->value);
    }

    public function isPublished(): bool
    {
        return in_array($this->statusValue(), ['published', 'approved'], true);
    }

    public function isPendingAnnotation(): bool
    {
        return in_array($this->statusValue(), [
            ObservationStatus::PendingAnnotation->value,
            'pending',
            'pending_annotation',
            'processing_ai',
        ], true);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function annotation(): HasOne
    {
        return $this->hasOne(Annotation::class);
    }

    public function scopePublished($query)
    {
        return $query
            ->whereIn('status', ['published', 'approved'])
            ->whereNotNull('slug')
            ->where('slug', '!=', '');
    }

    public function scopePendingAnnotation($query)
    {
        return $query->whereIn('status', [
            ObservationStatus::PendingAnnotation->value,
            'pending',
            'pending_annotation',
            'processing_ai',
        ]);
    }

    public function storedPhotoPath(): ?string
    {
        foreach ($this->photoPathCandidates() as $candidate) {
            $normalized = self::normalizeStoragePath($candidate);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    public function photoExistsOnDisk(): bool
    {
        return $this->absolutePhotoPath() !== null;
    }

    public function absolutePhotoPath(): ?string
    {
        foreach ($this->photoPathCandidates() as $candidate) {
            if (! is_string($candidate) || $candidate === '') {
                continue;
            }

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $path = $this->storedPhotoPath();

        if ($path === null) {
            return null;
        }

        $absolute = Storage::disk('public')->path($path);

        return is_file($absolute) ? $absolute : null;
    }

    /**
     * @return list<string|null>
     */
    private function photoPathCandidates(): array
    {
        return [
            $this->attributes['photo_path'] ?? null,
            $this->attributes['image_path'] ?? null,
            $this->attributes['thumbnail_path'] ?? null,
        ];
    }

    private static function normalizeStoragePath(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }

        if (is_file($path)) {
            $publicRoot = Storage::disk('public')->path('');

            if (str_starts_with($path, $publicRoot)) {
                $path = substr($path, strlen($publicRoot));
            } else {
                return null;
            }
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return $path !== '' ? $path : null;
    }

    public function getPhotoUrlAttribute(): string
    {
        foreach ($this->photoPathCandidates() as $candidate) {
            if (is_string($candidate) && (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://'))) {
                return $candidate;
            }
        }

        $path = $this->storedPhotoPath();

        if ($path === null) {
            return '';
        }

        if ($this->photoExistsOnDisk()) {
            return route('media.observation', $this);
        }

        return asset('storage/'.$path);
    }

    public function getContributorLabelAttribute(): string
    {
        return $this->guest_name ?: 'Anoniem';
    }

    public function hasAiSuggestion(): bool
    {
        return filled($this->attributes['ai_species'] ?? null)
            || filled($this->aiMeta('story_line'))
            || filled($this->attributes['ai_behavior'] ?? null);
    }

    public function isHeuristicSuggestion(): bool
    {
        return ($this->aiMeta('provider') ?? '') === 'heuristic';
    }

    public function aiProviderLabel(): ?string
    {
        $provider = $this->aiMeta('provider');

        return match ($provider) {
            'gemini' => 'Google Gemini',
            'openai' => 'OpenAI',
            'heuristic' => 'Basisvoorstel (zonder AI)',
            default => $provider,
        };
    }

    public function aiField(string $field): ?string
    {
        if ($this->annotation) {
            return null;
        }

        return match ($field) {
            'species' => $this->attributes['ai_species'] ?? null,
            'count_label' => isset($this->attributes['ai_count']) ? (string) $this->attributes['ai_count'] : null,
            'behavior' => $this->attributes['ai_behavior'] ?? null,
            'season' => $this->attributes['ai_season'] ?? null,
            'story_line' => $this->aiMeta('story_line'),
            'caption' => $this->aiMeta('caption'),
            default => null,
        };
    }

    public function suggestedField(string $field): ?string
    {
        $value = match ($field) {
            'species' => $this->attributes['ai_species'] ?? null,
            'count_label' => isset($this->attributes['ai_count']) ? (string) $this->attributes['ai_count'] : null,
            'behavior' => $this->attributes['ai_behavior'] ?? null,
            'season' => $this->attributes['ai_season'] ?? null,
            'story_line' => $this->aiMeta('story_line'),
            'caption' => $this->aiMeta('caption'),
            default => null,
        };

        if ($value === null) {
            return null;
        }

        return match ($field) {
            'story_line' => $this->limitSuggestedText($value, 200),
            'behavior' => $this->limitSuggestedText($value, 160),
            default => $value,
        };
    }

    private function limitSuggestedText(string $value, int $max): string
    {
        if (mb_strlen($value) <= $max) {
            return $value;
        }

        $truncated = mb_substr($value, 0, $max);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > (int) ($max * 0.6)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated, '.,;:!?…').'…';
    }

    public function aiConfidence(): ?int
    {
        return isset($this->attributes['ai_confidence']) ? (int) $this->attributes['ai_confidence'] : null;
    }

    public function aiMeta(string $key): ?string
    {
        $notes = $this->attributes['ai_notes'] ?? null;

        if (! is_string($notes) || $notes === '') {
            return null;
        }

        $data = json_decode($notes, true);

        if (! is_array($data) || ! isset($data[$key])) {
            return null;
        }

        $value = $data[$key];

        return is_string($value) || is_numeric($value) ? (string) $value : null;
    }

    public function publishFromAnnotation(Annotation $annotation): void
    {
        $base = Str::slug(Str::limit($annotation->story_line, 40, ''));
        $slug = $base !== '' ? $base.'-'.$this->id : 'moment-'.$this->id;

        $this->update([
            'status' => ObservationStatus::Published->value,
            'slug' => $slug,
            'published_at' => now(),
        ]);
    }

    public function markNotPublishable(): void
    {
        $status = 'rejected';

        $this->update([
            'status' => $status,
            'published_at' => null,
            'slug' => null,
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'status' => ObservationStatus::Unpublished->value,
        ]);
    }

    public function purge(): void
    {
        foreach ($this->storagePaths() as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $this->delete();
    }

    /**
     * @return list<string>
     */
    private function storagePaths(): array
    {
        $paths = [];

        foreach (['photo_path', 'image_path', 'thumbnail_path', 'share_card_path'] as $column) {
            $normalized = self::normalizeStoragePath($this->attributes[$column] ?? null);

            if ($normalized !== null) {
                $paths[] = $normalized;
            }
        }

        return array_values(array_unique($paths));
    }
}
