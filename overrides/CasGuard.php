<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as AuthGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class CasGuard implements AuthGuard
{
    private bool $loggedOut = false;

    private string $name = 'laravel-cas';

    private ?Authenticatable $user = null;

    public function __construct(
        private readonly ?UserProvider $provider,
        private readonly Request $request,
        private readonly Session $session
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function masquerade(){
        if (strtolower((string)config('app.env_real')) === 'production' && config('cas.cas_masquerade')) {
            throw new \Exception('Masquerade cannot be used in a production environment.');
        };
        $attributes = [
            "email" => config('cas.cas_masquerade')
        ];
        $user = User::firstOrCreateByAttributes($attributes);
        $this->setUser($user);
        return $user;
    }

    public function attempt(array $credentials): ?Authenticatable
    {

        $user = User::firstOrCreateByAttributes($credentials['attributes']);

        if (null === $user) {
            return null;
        }

        $this->setUser($user);

        return $user;
    }

    #[\Override]
    public function check()
    {
        return null !== $this->user();
    }

    public function getJsonParams()
    {
        return null;
    }

    public function getName()
    {
        return sprintf('login_%s_%s', $this->name, sha1(self::class));
    }

    #[\Override]
    public function guest()
    {
        return !$this->check();
    }

    #[\Override]
    public function hasUser()
    {
        return null !== $this->user();
    }

    #[\Override]
    public function id()
    {
        if ($this->loggedOut) {
            return null;
        }

        return $this->user->user;
    }

    public function logout(): void
    {
        $this->user = null;
        $this->loggedOut = true;
        $this->session->remove($this->getName());
        $this->session->migrate(true);
    }

    #[\Override]
    public function setUser(Authenticatable $user): void
    {

        $this->user = $user;
        $this->loggedOut = false;
        $this->session->put($this->getName(), $user);
        $this->session->migrate(true);
    }

    #[\Override]
    public function user()
    {
        if ($this->loggedOut) {
            return null;
        }

        return $this->session->get(auth()->guard('web')->getName());
//        return $this->provider->retrieveCasUser();
    }

    #[\Override]
    public function validate(array $credentials = [])
    {
        return [] !== $credentials;
    }
}
