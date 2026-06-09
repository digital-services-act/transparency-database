<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Platform extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const LABEL_DSA_TEAM = 'DSA Team';

    protected $hidden = [
        'deleted_at',
        'updated_at',
        'updated_by',
        'created_at',
        'created_by',
        'uuid',
        'user_id',
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'uuid',
    ];

    public function scopeNonDsa(Builder $query): void
    {
        $query->where('name', '!=', self::LABEL_DSA_TEAM);
    }

    public function scopeVlops(Builder $query): void
    {
        $query->where('name', '!=', self::LABEL_DSA_TEAM)->where('vlop', 1);
    }

    public function scopeNonVlops(Builder $query): void
    {
        $query->where('name', '!=', self::LABEL_DSA_TEAM)->where('vlop', 0);
    }

    public function isDSA()
    {
        return $this->name === self::LABEL_DSA_TEAM;
    }

    public static function getDsaPlatform(): Model|Builder|null
    {
        return self::query()->where('name', self::LABEL_DSA_TEAM)->first();
    }

    public static function dsaTeamPlatformId(): int
    {
        return self::getDsaPlatform()->id;
    }

    public function slugifyName()
    {
        return Str::slug($this->name);
    }

    #[\Override]
    protected static function boot()
    {
        parent::boot();
        static::creating(static function ($platform) {
            $platform->uuid = Str::uuid();
            $platform->created_by = auth()->user()->id ?? null;
        });
        static::updating(static function ($platform) {
            $platform->updated_by = auth()->user()->id ?? null;
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

    public function form_statements()
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id')->where('method', 'FORM');
    }

    public function api_statements(): HasMany
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id')->where('method', 'API');
    }

    public function api_multi_statements(): HasMany
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id')->where('method', 'API_MULTI');
    }
}
