<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Auth;

use EcPhp\LaravelCas\Auth\User\CasUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;

use function array_key_exists;

final class CasUserProvider implements UserProvider
{
    private string $guard_name = 'laravel-cas';

    private Authenticatable $model;

    public function __construct(
        private Session $session
    ) {}

    public function getModel(): ?Authenticatable
    {
        return $this->model;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false) {}

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if ([] === $credentials) {
            return null;
        }

        if (false === array_key_exists('user', $credentials)) {
            return null;
        }
        $this->model = new CasUser($credentials);

        return $this->model;
    }

    public function retrieveById($identifier)
    {
        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function retrieveCasUser(): ?Authenticatable
    {
        return $this->session->get(auth()->guard($this->guard_name)->getName());
    }

    public function updateRememberToken(Authenticatable $user, $token) {}

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }
}
