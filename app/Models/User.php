<?php

namespace App\Models;

use App\Services\InvitationService;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

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

    //We do not use the laravel eloquent relationship as EU Login emails are mix of uppercase and lowercase
    //Mysql is case-insensitive but not sqlite.

    public function getInvitation(): ?Invitation
    {
        return Invitation::firstWhere([
            'email' => strtolower((string)$this->email)
        ]);
    }

    public function acceptInvitation(): bool
    {
        $invitation = $this->getInvitation();

        if (is_null($invitation)) {
            return false;
        }

        if (strtolower((string)$this->email) !== strtolower((string)$invitation->email)) {
            return false;
        }


        // Link user to the platform
        $this->platform_id = $invitation->platform_id;

        // Give Contributor rights to the user
        $this->assignRole('Contributor');

        $this->save();

        // Delete the invitation
        $invitation->delete();

        return true;
    }

    public function platform(): HasOne
    {
        return $this->hasOne(Platform::class, 'id', 'platform_id');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(Statement::class, 'user_id', 'id');
    }

    public function invitation(): HasOne
    {
        return $this->hasOne(Invitation::class, 'email', 'email');
    }
}
