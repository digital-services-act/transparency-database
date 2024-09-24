<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Middleware;

use Closure;
use EcPhp\CasLib\Contract\CasInterface;
use EcPhp\CasLib\Contract\Response\Type\ServiceValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CasAuthenticator
{
    public function __construct(
        private CasInterface $cas,
        private ServerRequestInterface $serverRequest
    ) {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if (!$this->cas->supportAuthentication($this->serverRequest)) {
            return $next($request);
        }

        /** @var ServiceValidate $response */
        $response = $this->cas->requestTicketValidation($this->serverRequest);

        // Attempt to authenticate the user with the credentials provided by CAS
        if (auth('web')->attempt($response->getCredentials())) {
            // If the user was trying to access a protected route, redirect them back to that route
            return Redirect::intended(route('home'));
        }

        return redirect(route(config('laravel-cas.redirect_login_route')));

    }
}
