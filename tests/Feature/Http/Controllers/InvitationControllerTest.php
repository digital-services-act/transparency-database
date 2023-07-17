<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\InvitationController
 */
class InvitationControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFullySeededDatabase();
        $this->signInAsAdmin();


    }

    /**
     * @test
     */
    public function index_displays_view(): void
    {
        $invitations = Invitation::factory()->count(3)->create();

        $response = $this->get(route('invitation.index'));

        $response->assertOk();
        $response->assertViewIs('invitation.index');
        $response->assertViewHas('invitations');
    }


    /**
     * @test
     */
    public function create_displays_view(): void
    {
        $response = $this->get(route('invitation.create'));

        $response->assertOk();
        $response->assertViewIs('invitation.create');
    }


    /**
     * @test
     */
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\InvitationController::class,
            'store',
            \App\Http\Requests\InvitationStoreRequest::class
        );
    }

    /**
     * @test
     */
    public function store_saves_and_redirects(): void
    {
        $dummy_attributes = [
            'email' => 'newuser@test.com',
            'platform_id' => 5
        ];
        $response = $this->post(route('invitation.store', $dummy_attributes));

        $response->assertRedirect(route('invitation.index'));


        $this->assertDatabaseHas('invitations', $dummy_attributes);
    }


    /**
     * @test
     */
    public function show_displays_view(): void
    {
        $invitation = Invitation::factory()->create();

        $response = $this->get(route('invitation.show', $invitation));

        $response->assertOk();
        $response->assertViewIs('invitation.show');
        $response->assertViewHas('invitation');
    }


    /**
     * @test
     */
    public function edit_displays_view(): void
    {
        $invitation = Invitation::factory()->create();

        $response = $this->get(route('invitation.edit', $invitation));

        $response->assertOk();
        $response->assertViewIs('invitation.edit');
        $response->assertViewHas('invitation');
    }


    /**
     * @test
     */
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\InvitationController::class,
            'update',
            \App\Http\Requests\InvitationUpdateRequest::class
        );
    }


    /**
     * @test
     */
    public function destroy_deletes_and_redirects(): void
    {
        $invitation = Invitation::factory()->create();

        $response = $this->delete(route('invitation.destroy', $invitation));

        $response->assertRedirect(route('invitation.index'));

        $this->assertSoftDeleted($invitation);

    }
}
