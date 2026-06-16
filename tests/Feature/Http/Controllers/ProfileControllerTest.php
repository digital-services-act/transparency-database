<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    protected function createUserWithPlatform(bool $isVlop = false): User
    {
        $platform = Platform::factory()->create(['vlop' => $isVlop]);
        $user = User::factory()->create(['platform_id' => $platform->id]);
        $user->givePermissionTo('create statements');

        return $user;
    }

    public function test_profile_requires_authentication(): void
    {
        $response = $this->get(route('profile.start'));
        $response->assertStatus(302);
    }

    public function test_profile_displays_platform_statistics(): void
    {
        // Create some test data
        $vlopPlatform = Platform::factory()->create(['vlop' => true]);
        $nonVlopPlatform = Platform::factory()->create(['vlop' => false]);
        $user = $this->createUserWithPlatform();

        ElasticMocker::fake()->sqlRowsReturn([
            [5, Statement::METHOD_API, $vlopPlatform->id],
            [7, Statement::METHOD_FORM, $nonVlopPlatform->id],
        ]);

        // Mock the TokenService
        $this->mock(TokenService::class, function ($mock) {
            $mock->shouldReceive('getTotalVlopValidTokens')->once()->andReturn(1);
            $mock->shouldReceive('getTotalNonVlopValidTokens')->once()->andReturn(1);
        });

        $response = $this->actingAs($user)->get(route('profile.start'));

        $response->assertStatus(200);
        $response->assertViewIs('profile');
        $response->assertViewHas([
            'has_platform',
            'platform_name',
            'platform_ids_methods_data',
            'vlop_count',
            'non_vlop_count',
            'total_vlop_platforms_sending',
            'total_vlop_platforms_sending_api',
            'total_vlop_platforms_sending_webform',
            'total_non_vlop_platforms_sending',
            'total_non_vlop_platforms_sending_api',
            'total_non_vlop_platforms_sending_webform',
            'total_vlop_valid_tokens',
            'total_non_vlop_valid_tokens',
        ]);
    }

    public function test_profile_uses_database_statistics_when_elasticsearch_is_not_configured(): void
    {
        config([
            'elasticsearch.enabled' => false,
            'elasticsearch.uri' => [],
        ]);

        Statement::query()->delete();

        $vlopPlatform = Platform::factory()->create(['vlop' => true]);
        $nonVlopPlatform = Platform::factory()->create(['vlop' => false]);
        $vlopUser = User::factory()->create(['platform_id' => $vlopPlatform->id]);
        $nonVlopUser = User::factory()->create(['platform_id' => $nonVlopPlatform->id]);
        $user = $this->createUserWithPlatform();

        Statement::factory()->count(2)->create([
            'platform_id' => $vlopPlatform->id,
            'user_id' => $vlopUser->id,
            'method' => Statement::METHOD_API,
        ]);
        Statement::factory()->create([
            'platform_id' => $vlopPlatform->id,
            'user_id' => $vlopUser->id,
            'method' => Statement::METHOD_API_MULTI,
        ]);
        Statement::factory()->count(3)->create([
            'platform_id' => $nonVlopPlatform->id,
            'user_id' => $nonVlopUser->id,
            'method' => Statement::METHOD_FORM,
        ]);

        $this->mock(TokenService::class, function ($mock) {
            $mock->shouldReceive('getTotalVlopValidTokens')->once()->andReturn(4);
            $mock->shouldReceive('getTotalNonVlopValidTokens')->once()->andReturn(5);
        });

        $response = $this->actingAs($user)->get(route('profile.start'));

        $response->assertStatus(200);
        $methodsByPlatform = $response->viewData('platform_ids_methods_data');
        $this->assertEquals(2, $methodsByPlatform[$vlopPlatform->id][Statement::METHOD_API]);
        $this->assertEquals(1, $methodsByPlatform[$vlopPlatform->id][Statement::METHOD_API_MULTI]);
        $this->assertEquals(0, $methodsByPlatform[$vlopPlatform->id][Statement::METHOD_FORM]);
        $this->assertEquals(3, $methodsByPlatform[$nonVlopPlatform->id][Statement::METHOD_FORM]);

        $this->assertEquals(1, $response->viewData('total_vlop_platforms_sending'));
        $this->assertEquals(1, $response->viewData('total_vlop_platforms_sending_api'));
        $this->assertEquals(0, $response->viewData('total_vlop_platforms_sending_webform'));
        $this->assertEquals(1, $response->viewData('total_non_vlop_platforms_sending'));
        $this->assertEquals(0, $response->viewData('total_non_vlop_platforms_sending_api'));
        $this->assertEquals(1, $response->viewData('total_non_vlop_platforms_sending_webform'));
        $this->assertEquals(4, $response->viewData('total_vlop_valid_tokens'));
        $this->assertEquals(5, $response->viewData('total_non_vlop_valid_tokens'));
        $this->assertDatabaseHas('platforms', [
            'id' => $vlopPlatform->id,
            'has_statements' => 1,
        ]);
        $this->assertDatabaseHas('platforms', [
            'id' => $nonVlopPlatform->id,
            'has_statements' => 1,
        ]);
    }

    public function test_api_index_requires_authentication(): void
    {
        $response = $this->get(route('profile.api.index'));
        $response->assertStatus(302);
    }

    public function test_api_index_requires_create_statements_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.api.index'));
        $response->assertForbidden();
    }

    public function test_api_index_creates_token_if_none_exists(): void
    {
        $user = $this->createUserWithPlatform();

        $response = $this->actingAs($user)->get(route('profile.api.index'));

        $response->assertStatus(200);
        $response->assertViewIs('api');
        $response->assertViewHas('token_plain_text');

        // Verify token was created
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => User::API_TOKEN_KEY,
        ]);

        // Verify platform has_tokens was updated
        $this->assertDatabaseHas('platforms', [
            'id' => $user->platform->id,
            'has_tokens' => 1,
        ]);
    }

    public function test_api_index_does_not_create_token_if_exists(): void
    {
        $user = $this->createUserWithPlatform();
        $token = $user->createToken(User::API_TOKEN_KEY);

        $response = $this->actingAs($user)->get(route('profile.api.index'));

        $response->assertStatus(200);
        $response->assertViewIs('api');
        $response->assertViewHas('token_plain_text', null);

        // Verify no new token was created
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_new_token_requires_authentication(): void
    {
        $response = $this->post(route('profile.api.new-token'));
        $response->assertStatus(302);
    }

    public function test_new_token_requires_create_statements_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('profile.api.new-token'));
        $response->assertForbidden();
    }

    public function test_new_token_deletes_old_and_redirects(): void
    {
        $user = $this->createUserWithPlatform();
        $oldToken = $user->createToken(User::API_TOKEN_KEY);

        $response = $this->actingAs($user)
            ->post(route('profile.api.new-token'));

        $response->assertRedirect(route('profile.api.index'));

        // Verify old token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $oldToken->accessToken->id,
        ]);
    }
}
