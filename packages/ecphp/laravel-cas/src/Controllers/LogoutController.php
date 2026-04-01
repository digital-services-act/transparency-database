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

final class LogoutController extends Controller
{
    public function __invoke(
        Request $request,
        CasInterface $cas,
        ServerRequestInterface $serverRequest
    ): Redirector|RedirectResponse|ResponseInterface {
        $response = $cas
            ->logout(
                $serverRequest->withQueryParams(
                    $request->query->all()
                )
            );

        if (auth()->check()) {
            auth()->logout();

            return redirect('/');
        }

        return $response;
    }
}
