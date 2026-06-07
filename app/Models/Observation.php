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
        'photo_path',
        'contributor_note',
        'exif_taken_at',
        'status',
        'slug',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ObservationStatus::class,
            'exif_taken_at' => 'datetime',
            'published_at' => 'datetime',
        ];
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
        return $query->where('status', ObservationStatus::Published);
    }

    public function scopePendingAnnotation($query)
    {
        return $query->where('status', ObservationStatus::PendingAnnotation);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getPhotoUrlAttribute(): string
    {
        return asset('storage/'.$this->photo_path);
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
            'status' => ObservationStatus::Published,
            'slug' => $slug,
            'published_at' => now(),
        ]);
    }

    public function markNotPublishable(): void
    {
        $this->update([
            'status' => ObservationStatus::NotPublishable,
            'published_at' => null,
            'slug' => null,
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'status' => ObservationStatus::Unpublished,
        ]);
    }
}
