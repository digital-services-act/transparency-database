<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;

    public const API_TOKEN_KEY = 'api-token';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'platform_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function firstOrCreateByAttributes($attributes)
    {
        if (!isset ($attributes['email'])) {
            throw new Exception("Fatal Error: CAS callback did not contain an email");
        }

        $attributes['password'] = Str::random(16);

        return User::firstOrCreate(
            [
                'email' => $attributes['email'],
            ],
            $attributes
        );
    }

    public function hasValidApiToken(): bool
    {
        foreach ($this->tokens()->get() as $token) {
            if ($token->expires_at === null || $token->expires_at >= Carbon::now()) {
                return true;
            }
        }
        return false;
    }

    public function hasValidApiTokenHuman(): string
    {
        return $this->hasValidApiToken() ? 'Yes' : 'No';
    }


    public function platform(): HasOne
    {
        return $this->hasOne(Platform::class, 'id', 'platform_id');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(Statement::class, 'user_id', 'id');
    }

}
