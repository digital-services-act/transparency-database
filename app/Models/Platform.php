<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Platform extends Model
{
    use HasFactory, SoftDeletes;

    public const LABEL_DSA_TEAM = 'DSA Team';

    public const PLATFORM_TYPE_SOCIAL_MEDIA = 'Social Media';
    public const PLATFORM_TYPE_VIDEO = 'Video';
    public const PLATFORM_TYPE_MUSIC = 'Music';
    public const PLATFORM_TYPE_PHOTOGRAPHY = 'Photography';
    public const PLATFORM_TYPE_OTHER = 'Other';

    public const PLATFORM_TYPES = [
        'PLATFORM_TYPE_SOCIAL_MEDIA' => self::PLATFORM_TYPE_SOCIAL_MEDIA,
        'PLATFORM_TYPE_VIDEO' => self::PLATFORM_TYPE_VIDEO,
        'PLATFORM_TYPE_MUSIC' => self::PLATFORM_TYPE_MUSIC,
        'PLATFORM_TYPE_PHOTOGRAPHY' => self::PLATFORM_TYPE_PHOTOGRAPHY,
        'PLATFORM_TYPE_OTHER' => self::PLATFORM_TYPE_OTHER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'platform_id', 'id');
    }

    public function statements()
    {
        return $this->hasManyThrough(Statement::class, User::class, 'platform_id', 'user_id', 'id', 'id');
    }
}
