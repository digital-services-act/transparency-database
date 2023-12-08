<?php

namespace Tests\Feature\Auth;

use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationService;
use App\Services\StatementStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OnboardingTest extends TestCase
{

    use RefreshDatabase;

    protected InvitationService $invitation_service;

    /**
     * @return void
     * @test
     */
    public function onboarding_user_should_be_able_to_generate_api_key(): void
    {
        $this->setUpFullySeededDatabase();

        $boardingUser = $this->signInAsOnboarding();

        $this->get(route('profile.start'))->assertOk();
        $this->get(route('profile.api.index'))->assertOk();




    }




}
