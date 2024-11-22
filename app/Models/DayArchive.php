<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayArchive extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'date' => 'date:Y-m-d'
    ];

    public function scopeGlobal(Builder $query): void
    {
        $query->whereNull('platform_id');
    }

    /**
     * @return BelongsTo
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
