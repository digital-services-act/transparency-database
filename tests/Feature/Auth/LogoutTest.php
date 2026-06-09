<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LogoutTest extends TestCase
{
    #[\Override]
    protected function setUpFullySeededDatabase($statement_count = 10): void
    {
        // The logout route only needs auth/session configuration.
    }

    public function test_cas_logout_route_resolves_the_package_guard(): void
    {
        config([
            'laravel-cas.masquerade' => 'test@example.org',
            'laravel-cas.redirect_logout_url' => 'https://example.org/logged-out',
        ]);

        $this->get('/logout')
            ->assertRedirect('https://example.org/logged-out');
    }
}
