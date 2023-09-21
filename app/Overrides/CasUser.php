<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Auth\User;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\Access\Authorizable as AuthorizableTrait;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;


final class CasUser implements Authenticatable, Authorizable
{

    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, AuthorizableTrait, AuthenticatableTrait;

    /**
     * The user storage.
     *
     * @var array<mixed>
     */
    private array $storage;

    /**
     * CasUser constructor.
     *
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->storage = $data;
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function __toString(): string
    {
        return $this->get('user');
    }

    public function get(string $key, $default = null)
    {
        return $this->getStorage()[$key] ?? $default;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->getStorage()['attributes'][$key] ?? $default;
    }

    public function getAttributes(): array
    {
        return $this->get('attributes', []);
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier(): string
    {
        return $this->get('user');
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName(): string
    {
        return 'user';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword(): ?string
    {
        return null;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getPgt(): ?string
    {
        return $this->get('proxyGrantingTicket');
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken(): string
    {
        return null;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName(): ?string
    {
        return null;
    }

    public function getRoles(): array
    {
        return ['ROLE_CAS_AUTHENTICATED'];
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param mixed $value
     */
    public function setRememberToken($value): void
    {
    }

    /**
     * Get the storage.
     *
     * @return array<mixed>
     */
    private function getStorage(): array
    {
        return $this->storage;
    }

    public function getAttributeValue($attribute)
    {
//        dd($attribute);
        return true;
    }
}
