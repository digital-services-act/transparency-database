<?php

namespace Tests\Feature\Routing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\InvitationController
 */
class AnalyticsRoutingTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFullySeededDatabase();

    }

    /**
     * @test
     */
    public function regular_users_cant_access_analytics(): void
    {

        $response = $this->get(route('analytics.index'));


        $response->assertRedirectContains('/login');

    }

    /**
     * @test
     */
    public function contributors_can_access_analytics(): void
    {

        $this->signInAsContributor();

        $response = $this->get(route('analytics.index'));

        $response->assertOk();


    }

    /**
     * @test
     */
    public function admin_users_can_access_analytics(): void
    {

        $this->signInAsAdmin();

        $response = $this->get(route('analytics.index'));

        $response->assertOk();
    }


}
