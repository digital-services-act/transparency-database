<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RouteProtectionTest extends TestCase
{


    use RefreshDatabase;

    private $visitor;

    private $contributor;

    private $onboarding;

    private $support;

    #[\Override]
    protected function setup(): void
    {
        parent::setUp();

        $this->visitor = User::factory()->create();

        $this->contributor = User::factory()->create()->assignRole('Contributor');
        $this->onboarding = User::factory()->create()->assignRole('Onboarding');
        $this->support = User::factory()->create()->assignRole('Support');
        $this->researcher = User::factory()->create()->assignRole('Researcher');
    }

    /**
     * @return void
     * @test
     */
    public function generate_api_key(): void
    {
        $this->withExceptionHandling();

        $rejected = [$this->visitor, $this->support];
        $allowed = [$this->contributor, $this->onboarding, $this->researcher];

        $this->check_route('profile.api.index', $allowed, $rejected);
        $this->check_route_for_text('profile.start', $allowed, $rejected,'API Token');

    }


    /**
     * @return void
     * @test
     */
    public function manage_platforms(): void
    {
        $this->withExceptionHandling();

        $rejected = [$this->visitor, $this->contributor, $this->researcher];
        $allowed = [$this->onboarding, $this->support];

        $this->check_route('platform.create', $allowed, $rejected);
        $this->check_route_for_text('profile.start', $allowed, $rejected,'Manage Platforms');
    }

    /**
     * @return void
     * @test
     */
    public function manage_users(): void
    {
        $this->withExceptionHandling();

        $rejected = [$this->visitor, $this->contributor, $this->researcher];
        $allowed = [$this->onboarding, $this->support];

        $this->check_route('user.create', $allowed, $rejected);
        $this->check_route_for_text('profile.start', $allowed, $rejected,'Manage Users');
    }



    /**
     * @return void
     * @test
     */
    public function onboarding_dashboard(): void
    {
        $this->withExceptionHandling();

        $route= 'onboarding.index';

        $rejected = [$this->visitor, $this->contributor, $this->researcher];
        $allowed = [$this->support, $this->onboarding];

        $this->check_route($route, $allowed, $rejected);
        $this->check_route_for_text($route, $allowed, $rejected, 'Onboarding Dashboard');

    }

    /**
     * @return void
     * @test
     */
    public function view_log_files(): void
    {
        $this->withExceptionHandling();

        $route= 'log-messages.index';

        $rejected = [$this->visitor, $this->contributor, $this->onboarding, $this->researcher];
        $allowed = [$this->support];

        $this->check_route($route, $allowed, $rejected);
//        $this->check_route_for_text($route, $allowed, $rejected, 'Log Messages');

    }

    public function test_ensure_that_force_authentication_is_working(): void
    {
        Config::set('app.env_real', 'dev');
        $this->get('/')->assertRedirect();
    }


    private function check_route($route, $allowed, $restricted): void
    {
        foreach ($restricted as $restricted_user) {
            $this->signIn($restricted_user);
            $this->get(route($route))->assertForbidden();
        }

        foreach ($allowed as $allowed_user) {
            $this->signIn($allowed_user);
            $this->get(route($route))->assertOk();
        }
    }

    private function check_route_for_text($route, $allowed, $restricted, $text): void
    {
        foreach ($restricted as $restricted_user) {
            $this->signIn($restricted_user);
            $this->get(route($route))->assertDontSeeText($text);
        }

        foreach ($allowed as $allowed_user) {
            $this->signIn($allowed_user);
            $this->get(route($route))->assertSeeText($text);
        }
    }


}
