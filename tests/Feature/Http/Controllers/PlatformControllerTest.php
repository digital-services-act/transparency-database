<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;

class PlatformControllerTest extends TestCase
{
    // use AdditionalAssertions;
    use RefreshDatabase;

    /**
     * @test
     */
    public function index_displays_platforms(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::factory()->create(['name' => 'Test Platform']);

        $response = $this->get(route('platform.index'));

        $response->assertOk();
        $response->assertViewIs('platform.index');
        $response->assertViewHas('platforms');
        $response->assertSee('Test Platform');
    }

    /**
     * @test
     */
    public function index_can_search_platforms(): void
    {
        $this->signInAsAdmin();
        $platform1 = Platform::factory()->create(['name' => 'Test Platform']);
        $platform2 = Platform::factory()->create(['name' => 'Another Platform']);

        $response = $this->get(route('platform.index', ['s' => 'Test']));

        $response->assertOk();
        $response->assertSee('Test Platform');
        $response->assertDontSee('Another Platform');
    }

    /**
     * @test
     */
    public function create_displays_form(): void
    {
        $this->signInAsAdmin();

        $response = $this->get(route('platform.create'));

        $response->assertOk();
        $response->assertViewIs('platform.create');
        $response->assertViewHas(['platform', 'options']);
    }

    /**
     * @test
     */
    public function store_creates_platform(): void
    {
        $this->signInAsAdmin();

        $response = $this->post(route('platform.store'), [
            'name' => 'New Platform',
            'dsa_common_id' => 'DSA123',
            'vlop' => 1,
            'onboarded' => 1,
        ]);

        $response->assertRedirect(route('platform.index'));
        $this->assertDatabaseHas('platforms', [
            'name' => 'New Platform',
            'dsa_common_id' => 'DSA123',
        ]);
    }

    /**
     * @test
     */
    public function store_prevents_dsa_team_name(): void
    {
        $this->signInAsAdmin();

        $response = $this->post(route('platform.store'), [
            'name' => Platform::LABEL_DSA_TEAM,
            'vlop' => 1,
        ]);

        $response->assertRedirect(route('platform.index'));
        $response->assertSessionHas('error');
    }

    /**
     * @test
     */
    public function show_redirects_to_index(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::factory()->create();

        $response = $this->get(route('platform.show', $platform));

        $response->assertRedirect(route('platform.index'));
    }

    /**
     * @test
     */
    public function edit_displays_form(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::factory()->create();

        $response = $this->get(route('platform.edit', $platform));

        $response->assertOk();
        $response->assertViewIs('platform.edit');
        $response->assertViewHas(['platform', 'options']);
    }

    /**
     * @test
     */
    public function edit_stores_returnto(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::factory()->create();

        $response = $this->get(route('platform.edit', [
            'platform' => $platform,
            'returnto' => '/some/path',
        ]));

        $response->assertOk();
        $this->assertEquals('/some/path', session('returnto'));
    }

    /**
     * @test
     */
    public function update_saves_platform(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::factory()->create();

        $response = $this->put(route('platform.update', $platform), [
            'name' => 'Updated Platform',
            'dsa_common_id' => 'DSA456',
            'vlop' => 0,
            'onboarded' => 1,
            'has_tokens' => 1,
            'has_statements' => 1,
        ]);

        $response->assertRedirect(route('platform.index'));
        $this->assertDatabaseHas('platforms', [
            'id' => $platform->id,
            'name' => 'Updated Platform',
            'dsa_common_id' => 'DSA456',
        ]);
    }

    /**
     * @test
     */
    public function update_respects_returnto(): void
    {
        $this->signInAsAdmin();
        $platform = Platform::factory()->create();
        session(['returnto' => '/some/path']);

        $response = $this->put(route('platform.update', $platform), [
            'name' => 'Updated Platform',
            'dsa_common_id' => 'DSA456',
            'vlop' => 0,
        ]);

        $response->assertRedirect('/some/path');
    }

    /**
     * @test
     */
    public function update_prevents_dsa_platform_changes(): void
    {
        $this->signInAsAdmin();
        $dsa_platform = Platform::getDsaPlatform();

        $response = $this->put(route('platform.update', $dsa_platform), [
            'name' => 'Changed DSA',
            'dsa_common_id' => 'DSA789',
            'vlop' => 0,
        ]);

        $response->assertRedirect(route('platform.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('platforms', [
            'id' => $dsa_platform->id,
            'name' => 'Changed DSA',
        ]);
    }

    /**
     * @test
     */
    public function deleting_platform_deletes_the_rest(): void
    {
        $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $total_users_start = User::count();

        $statement = Statement::all()->random(); // Grab one
        $user = $statement->user;
        $platform = $user->platform;

        $platform_count = Platform::all()->count();
        $statement_count = $platform->statements()->get()->count(); // at least 1
        $user_count = $platform->users()->get()->count(); // at least 1

        $dsa_platform = Platform::getDsaPlatform();
        $dsa_platform_statement_count = $dsa_platform->statements()->count();
        $this->assertEquals(0, $dsa_platform_statement_count); // DSA should have no statements

        // delete the platform and assert we deleted
        $this->delete(route('platform.destroy', [$platform]))->assertRedirect(route('platform.index'));

        // Statements should have moved to DSA
        $dsa_platform_statement_count = $dsa_platform->statements()->count(); // DSA should have statements
        $this->assertEquals($statement_count, $dsa_platform_statement_count);

        $this->assertCount(10, Statement::all());
        $this->assertCount($total_users_start - $user_count, User::all());
        $this->assertCount($platform_count - 1, Platform::all());
    }

    /**
     * @test
     */
    public function destroy_requires_admin(): void
    {
        $this->signIn();
        $platform = Platform::factory()->create();

        $response = $this->delete(route('platform.destroy', $platform));

        $response->assertForbidden();
        $this->assertDatabaseHas('platforms', ['id' => $platform->id]);
    }

    /**
     * @test
     */
    public function destroy_prevents_dsa_platform_deletion(): void
    {
        $this->signInAsAdmin();
        $dsa_platform = Platform::getDsaPlatform();

        $response = $this->delete(route('platform.destroy', $dsa_platform));

        $response->assertRedirect(route('platform.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('platforms', ['id' => $dsa_platform->id]);
    }
}
