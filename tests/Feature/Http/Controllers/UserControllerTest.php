<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function index_displays_users_with_search(): void
    {
        $this->signInAsAdmin();
        $user = User::factory()->create(['email' => 'searchme@test.com']);

        $response = $this->get(route('user.index', ['s' => 'searchme']));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewHas('users');
        $response->assertSee('searchme@test.com');
    }

    /**
     * @test
     */
    public function index_filters_by_platform_uuid(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::factory()->create();
        $user = User::factory()->create(['email' => 'searchme@test.com', 'platform_id' => $platform->id]);

        $url = route('user.index', ['uuid' => $platform->uuid->toString()]);
        $response = $this->get($url);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewHas('users');
        $response->assertSee($user->email);
    }

    /**
     * @test
     */
    public function create_stores_returnto_in_session(): void
    {
        $this->signInAsAdmin();
        $returnTo = '/some/path';

        $response = $this->get(route('user.create', ['returnto' => $returnTo]));

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($returnTo, session('returnto'));
    }

    /**
     * @test
     */
    public function create_sets_platform_from_query(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::factory()->create();

        $response = $this->get(route('user.create', ['platform_id' => $platform->id]));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewHas('user');
        $response->assertSee('value="' . $platform->id . '"', false);
    }

    /**
     * @test
     */
    public function store_redirects_to_returnto_url(): void
    {
        $this->signInAsAdmin();
        $returnTo = '/some/path';
        session(['returnto' => $returnTo]);

        $response = $this->post(route('user.store'), [
            'email' => 'test@example.com',
            'roles' => [1],
            'platform_id' => 1
        ]);

        $response->assertRedirect($returnTo);
        $this->assertNull(session('returnto'));
    }

    /**
     * @test
     */
    public function update_changes_user_details(): void
    {
        $this->signInAsAdmin();
        $user = User::factory()->create();
        $newPlatform = Platform::factory()->create();

        $response = $this->put(route('user.update', $user), [
            'email' => 'updated@example.com',
            'platform_id' => $newPlatform->id,
            'roles' => [1]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
            'platform_id' => $newPlatform->id
        ]);
    }

    /**
     * @test
     */
    public function update_redirects_to_returnto_url(): void
    {
        $this->signInAsAdmin();
        $user = User::factory()->create();
        $returnTo = '/some/path';
        session(['returnto' => $returnTo]);

        $response = $this->put(route('user.update', $user), [
            'email' => 'test@example.com',
            'platform_id' => 1,
            'roles' => [1]
        ]);

        $response->assertRedirect($returnTo);
        $this->assertNull(session('returnto'));
    }

    /**
     * @test
     */
    public function show_redirects_to_index(): void
    {
        $this->signInAsAdmin();
        $user = User::factory()->create();

        $response = $this->get(route('user.show', $user));

        $response->assertRedirect(route('user.index'));
    }

    /**
     * @test
     */
    public function edit_stores_returnto_in_session(): void
    {
        $this->signInAsAdmin();
        $user = User::factory()->create();
        $returnTo = '/some/path';

        $response = $this->get(route('user.edit', ['user' => $user, 'returnto' => $returnTo]));

        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($returnTo, session('returnto'));
    }

    /**
     * @test
     */
    public function destroy_redirects_to_returnto_url(): void
    {
        $this->signInAsAdmin();
        $user = User::factory()->create();
        $returnTo = '/some/path';

        $response = $this->delete(route('user.destroy', ['user' => $user, 'returnto' => $returnTo]));

        $response->assertRedirect($returnTo);
    }

    /**
     * @test
     */
    public function non_admin_cannot_see_restricted_roles(): void
    {
        $user = $this->signInAsSupport();
        $controller = new \App\Http\Controllers\UserController();

        $roles = $controller->getAvailableRolesToDisplay();

        $this->assertCount(3, $roles); // Only Contributor, Support and Researcher roles should be visible
        $this->assertFalse($roles->contains('name', 'Admin'));
        $this->assertFalse($roles->contains('name', 'Onboarding'));
        $this->assertFalse($roles->contains('name', 'User'));
    }

    /** @test */
    public function deleting_user_deletes_the_rest(): void
    {
        /** @var User $user */
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $total_users_start = User::count();

        $statement = Statement::all()->random();
        $user = $statement->user;

        $statement_count = $user->statements()->get()->count(); // at least 1

        // delete the user and assert we deleted
        $this->delete(route('user.destroy', [$user]))->assertRedirect(route('user.index'));

        // the statements stay the platform id is in the statements.
        $this->assertCount(10, Statement::all());
        $this->assertCount($total_users_start - 1, User::all());
    }

    /** @test */
    public function support_should_be_able_to_create_user(): void
    {
        /** @var User $user */
        $user = $this->signInAsSupport();

        $user_count = User::count();

        $response = $this->post(route('user.store'), [
            'email' => 'foo@bar.com',
            'roles' => [1,2],
            'platform_id' => 1
        ], [
            'Accept' => 'application/json'
        ]);

        $this->assertCount($user_count + 1, User::all());
        $response->assertRedirect();
    }
}
