<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayArchive extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeGlobal(Builder $query): void
    {
        $query->whereNull('platform_id');
    }
}
