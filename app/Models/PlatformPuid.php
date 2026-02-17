<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlatformPuid extends Model
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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function platform(): HasOne
    {
        return $this->hasOne(Platform::class, 'id', 'platform_id');
    }

    public static function insertBulk(array $puids, int $platform_id): void
    {
        $timestamp = now();

        $records = array_map(function ($puid) use ($platform_id, $timestamp) {
            return [
                'platform_id' => $platform_id,
                'puid' => $puid,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $puids);

        self::insert($records);
    }
}
