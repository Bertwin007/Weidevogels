<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'location',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function findLjippelan(): ?self
    {
        return static::query()
            ->where(function (Builder $query): void {
                $query->whereIn('slug', ['ljippelan', 'ljippelân'])
                    ->orWhere('name', 'like', '%Ljippel%');
            })
            ->where(function (Builder $query): void {
                $query->where('active', true)->orWhereNull('active');
            })
            ->orderBy('id')
            ->first();
    }
}
