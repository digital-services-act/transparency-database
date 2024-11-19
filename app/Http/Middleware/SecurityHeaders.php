<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com code.jquery.com https://webtools.europa.eu *.webanalytics.europa.eu https://cdnjs.cloudflare.com https://europa.eu https://*.cloudfront.net; style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com code.jquery.com https://webtools.europa.eu https://*.cloudfront.net; img-src 'self' https://webtools.europa.eu *.webanalytics.europa.eu https://dsa-images-disk.s3.eu-central-1.amazonaws.com https://*.cloudfront.net data:; connect-src 'self' https://webtools.europa.eu *.webanalytics.europa.eu; frame-src 'self' https://app.powerbi.com; object-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';"
        );

        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'no-referrer');

        $response->headers->set('Strict-Transport-Security', 'Strict-Transport-Security: max-age=31536000; includeSubDomains');

        return $response;
    }
}
