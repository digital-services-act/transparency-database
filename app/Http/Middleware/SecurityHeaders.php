<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' unpkg.com cdnjs.cloudflare.com code.jquery.com  *.cloudfront.net  https://webtools.europa.eu *.europa.eu *.webanalytics.europa.eu https://cdnjs.cloudflare.com https://europa.eu *.cloudfront.net https://unpkg.com; style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com code.jquery.com https://webtools.europa.eu *.europa.eu *.cloudfront.net; img-src 'self' https://webtools.europa.eu *.webanalytics.europa.eu https://dsa-images-disk.s3.eu-central-1.amazonaws.com *.europa.eu *.cloudfront.net data:; connect-src 'self' *.cloudfront.net *.eu-central-1.elb.amazonaws.com *.europa.eu https://webtools.europa.eu *.webanalytics.europa.eu; frame-src 'self' https://app.powerbi.com; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';"
        );

        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        $response->headers->set('Strict-Transport-Security', 'Strict-Transport-Security: max-age=31536000; includeSubDomains');

        return $response;
    }
}
