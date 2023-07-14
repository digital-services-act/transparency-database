<?php

namespace App\Models;

use App\Services\InvitationService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    //We do not use the laravel eloquent relationship as EU Login emails are mix of uppercase and lowercase
    //Mysql is case-insensitive but not sqlite.

    public function getInvitation() : ?Invitation{
        return  Invitation::firstWhere([
            'email' => strtolower($this->email)
        ]);
    }

    public function acceptInvitation(): bool
    {
        $invitation = $this->getInvitation();

        if (is_null($invitation)) return false;

        if (strtolower($this->email) !== strtolower($invitation->email)) return false;


        // Link user to the platform
        $this->platform_id = $invitation->platform_id;

        // Give Contributor rights to the user
        $this->assignRole('Contributor');

        $this->save();

        // Delete the invitation
        $invitation->delete();

        return true;

    }

    public function setImpersonating($id)
    {
        session()->put('impersonate', $id);
    }

    public function stopImpersonating()
    {
        session()->forget('impersonate');
    }

    public function platform(): HasOne
    {
        return $this->hasOne(Platform::class, 'id', 'platform_id');
    }

    public function statements() : HasMany
    {
        return $this->hasMany(Statement::class, 'user_id', 'id');
    }

    public function invitation() : HasOne
    {
        return $this->hasOne(Invitation::class, 'email', 'email');
    }
}
