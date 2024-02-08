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
        'created_at',
        'uuid',
        'user_id',
    ];

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

    #[\Override]
    protected static function boot()
    {
        parent::boot();
        static::creating(static function ($statement) {
            $statement->uuid = Str::uuid();
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'platform_id', 'id');
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'platform_id', 'id');
    }

    public function statements()
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id');
    }

    public function form_statements()
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id')->where('method','FORM');
    }

    public function api_statements(): HasMany
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id')->where('method','API');
    }

    public function api_multi_statements(): HasMany
    {
        return $this->hasMany(Statement::class, 'platform_id', 'id')->where('method','API_MULTI');
    }
}
