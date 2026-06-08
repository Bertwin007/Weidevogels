<?php

namespace App\Models;

use App\Enums\ObservationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function getPhotoUrlAttribute(): string
    {
        $path = $this->attributes['photo_path'] ?? $this->attributes['image_path'] ?? null;

        return $path ? asset('storage/'.$path) : '';
    }

    public function getContributorLabelAttribute(): string
    {
        return $this->guest_name ?: 'Anoniem';
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
}
