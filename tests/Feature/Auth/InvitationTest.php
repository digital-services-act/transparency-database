<?php

namespace Tests\Feature\Auth;

use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{

    use RefreshDatabase;

    protected InvitationService $invitation_service;

    /**
     * @return void
     * @test
     */
    public function user_should_have_no_rights_after_login(): void
    {
        $this->setUpFullySeededDatabase();

        $randomUser = $this->signIn();

        $this->assertEmpty($randomUser->permissions);
        $this->assertEmpty($randomUser->roles);

    }

    /**
     * @return void
     * @test
     */
    public function invited_user_should_have_contributor_rights_after_login(): void
    {
        $this->setUpFullySeededDatabase();

        $user = User::factory()->create([
            'email' => "invited@testing.org",
        ]);

        Invitation::factory()->create(['email' => 'invited@testing.org']);

        $this->signIn($user);
        $user->acceptInvitation();

        $response = $this->get(route('statement.create'));
        $response->assertOk();

    }

    /**
     * @return void
     * @test
     */
    public function invited_user_with_mixedcase_email_should_have_contributor_rights_after_login(): void
    {
        $this->setUpFullySeededDatabase();

        $user = User::factory()->create([
            'email' => "invited@TESTING.org",
        ]);

        Invitation::factory()->create(['email' => 'invited@testing.org']);

        $this->signIn($user);
        $user->acceptInvitation();

        $response = $this->get(route('statement.create'));
        $response->assertOk();

    }

    /**
     * @return void
     * @test
     */
    public function non_invited_user_should_not_have_contributor_rights_after_login(): void
    {
        $this->setUpFullySeededDatabase();

        $user = User::factory()->create([
            'email' => "not_invited@testing.org",
        ]);

        Invitation::factory()->create(['email' => 'invited@testing.org']);

        $this->signIn($user);
        $user->acceptInvitation();

        $response = $this->get(route('statement.create'));
        $response->assertForbidden();

    }


}
