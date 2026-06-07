<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Annotation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_publishable' => 'boolean',
        ];
    }

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function annotator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'annotator_id');
    }
}
