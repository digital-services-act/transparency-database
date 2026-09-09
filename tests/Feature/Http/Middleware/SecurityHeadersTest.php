<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_it_sets_a_valid_strict_transport_security_header(): void
    {
        $response = (new SecurityHeaders())->handle(
            Request::create('/', 'GET'),
            static fn (): Response => new Response('OK'),
        );

        self::assertSame(
            'max-age=31536000; includeSubDomains',
            $response->headers->get('Strict-Transport-Security'),
        );
    }
}
