<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\LaravelCas\Controllers;

use EcPhp\CasLib\Contract\CasInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LoginController extends Controller
{
    public function __invoke(
        Request $request,
        CasInterface $cas,
        ServerRequestInterface $serverRequest,
    ): Redirector|RedirectResponse|ResponseInterface {
        if (strtolower((string)config('app.env_real')) !== 'production' && !is_null(config('cas.cas_masquerade'))) {
            auth('web')->masquerade();
            return redirect('/profile/start');
        }

        $parameters = $request->query->all() + [
                'renew' => null !== auth()->guard()->user(),
            ];

        return $cas->login($serverRequest->withQueryParams($parameters));
    }
}
