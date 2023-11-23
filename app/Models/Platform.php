<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Platform extends Model
{
    use HasFactory, SoftDeletes;

    public const LABEL_DSA_TEAM = 'DSA Team';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'uuid'
    ];

    public function scopeNonDsa(Builder $query): void
    {
        $query->where('name', '!=', self::LABEL_DSA_TEAM);
    }

    public function scopeVlops(Builder $query): void
    {
        $query->where('name', '!=', self::LABEL_DSA_TEAM)->where('vlop', 1);
    }

    public function isDSA()
    {
        return $this->name === self::LABEL_DSA_TEAM;
    }

    public static function getDsaPlatform()
    {
        return Platform::where('name', self::LABEL_DSA_TEAM)->first();
    }

    public function slugifyName()
    {
        return Str::slug($this->name);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($statement) {
            $statement->uuid = Str::uuid();
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'platform_id', 'id');
    }

    public function statements()
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id');
    }

    public function dayTotals()
    {
        return $this->hasMany(PlatformDayTotal::class, 'platform_id', 'id');
    }
}
