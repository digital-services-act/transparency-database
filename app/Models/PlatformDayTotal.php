<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformDayTotal extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d'
    ];

    public function platform()
    {
        return $this->hasOne(Platform::class, 'id', 'platform_id');
    }
}
