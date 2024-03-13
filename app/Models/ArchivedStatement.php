<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ArchivedStatement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'platform_id',
        'puid',
        'uuid',
        'date_received',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'date_received' => 'timestamp',
    ];

    public function platforms(): HasMany
    {
        return $this->hasMany(Platform::class);
    }

    public function statement(): HasOne
    {
        return $this->hasOne(Statement::class,'uuid','uuid');
    }
}
