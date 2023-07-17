<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    public const API_TOKEN_KEY = 'api-token';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'eu_login_username',
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

    public static function firstOrCreateByAttributes($attributes)
    {
        if (session()->has('impersonate')) {
            $user = User::where('id', session()->get('impersonate'))->first();
            return $user;

        }
        $attributes['password'] = Str::random(16);
        if (isset($attributes['domainUsername']) || isset($attributes['eu_login_username'])) {
            if (isset($attributes['domainUsername'])) $username = $attributes['domainUsername'];
            if (isset($attributes['eu_login_username'])) $username = $attributes['eu_login_username'];
            $attributes['name'] = isset($attributes['firstName']) && isset($attributes['lastName'])
                ? $attributes['firstName'] . ' ' . $attributes['lastName']
                : (isset($attributes['name'])
                    ? $attributes['name']
                    : '');

        }
        $user = User::firstOrCreate(
            [
                'eu_login_username' => $username ?? session()->get('cas_user'),
            ],
            $attributes
        );
        return $user;
    }

    public function setImpersonating($id)
    {
        session()->put('impersonate', $id);
    }

    public function stopImpersonating()
    {
        session()->forget('impersonate');
    }

    public function platform()
    {
        return $this->hasOne(Platform::class, 'id', 'platform_id');
    }

    public function statements()
    {
        return $this->hasMany(Statement::class, 'user_id', 'id');
    }
}
