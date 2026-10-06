<?php

namespace Tests\Feature\Auth;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CachedTokenAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['sanctum.guard' => [], 'sanctum.cache.store' => 'array']);
        Cache::store('array')->flush();
        $this->freezeTime();
    }

    public function test_cached_bearer_authentication_does_not_select_the_token_again(): void
    {
        $token = User::factory()->create()->createToken(User::API_TOKEN_KEY);

        DB::enableQueryLog();

        try {
            $this->bearerGet($token->plainTextToken)->assertOk();
            $this->assertNotEmpty($this->tokenSelectQueries());

            DB::flushQueryLog();

            $this->bearerGet($token->plainTextToken)->assertOk();
            $this->assertEmpty($this->tokenSelectQueries());
        } finally {
            DB::disableQueryLog();
        }
    }

    public function test_cached_token_id_does_not_authenticate_a_different_secret(): void
    {
        $token = User::factory()->create()->createToken(User::API_TOKEN_KEY);

        $this->bearerGet($token->plainTextToken)->assertOk();
        $this->bearerGet($token->accessToken->id.'|incorrect-secret')->assertUnauthorized();
        $this->bearerGet($token->plainTextToken)->assertOk();
    }

    public function test_a_cached_token_cannot_authenticate_after_its_expiration(): void
    {
        $token = User::factory()->create()->createToken(User::API_TOKEN_KEY, ['*'], now()->addMinute());

        $this->bearerGet($token->plainTextToken)->assertOk();

        $this->travel(61)->seconds();

        $this->bearerGet($token->plainTextToken)->assertUnauthorized();
    }

    public function test_a_cached_token_cannot_authenticate_after_global_expiration(): void
    {
        config(['sanctum.expiration' => 1]);
        $token = User::factory()->create()->createToken(User::API_TOKEN_KEY);

        $this->bearerGet($token->plainTextToken)->assertOk();

        $this->travel(61)->seconds();

        $this->bearerGet($token->plainTextToken)->assertUnauthorized();
    }

    public function test_a_cached_token_respects_a_shortened_global_expiration_policy(): void
    {
        $token = User::factory()->create()->createToken(User::API_TOKEN_KEY);

        $this->bearerGet($token->plainTextToken)->assertOk();

        $this->travel(61)->seconds();
        config(['sanctum.expiration' => 1]);

        $this->bearerGet($token->plainTextToken)->assertUnauthorized();
    }

    public function test_a_cached_token_cannot_authenticate_a_soft_deleted_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken(User::API_TOKEN_KEY);

        $this->bearerGet($token->plainTextToken)->assertOk();

        $user->delete();

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token->accessToken->id]);
        $this->bearerGet($token->plainTextToken)->assertUnauthorized();
    }

    public function test_cached_token_authentication_uses_current_user_platform_and_permissions(): void
    {
        Route::middleware('auth:sanctum')->get('/testing/cached-token-user', static function (Request $request) {
            return response()->json([
                'platform_id' => $request->user()->platform_id,
                'roles' => $request->user()->getRoleNames(),
                'can_create_statements' => $request->user()->can('create statements'),
            ]);
        });

        $initialPlatform = Platform::factory()->create();
        $newPlatform = Platform::factory()->create();
        $user = User::factory()->create(['platform_id' => $initialPlatform->id]);
        $user->assignRole('Contributor');
        $token = $user->createToken(User::API_TOKEN_KEY);

        $this->bearerGet($token->plainTextToken, '/testing/cached-token-user')
            ->assertOk()
            ->assertJsonPath('platform_id', $initialPlatform->id)
            ->assertJsonPath('roles.0', 'Contributor')
            ->assertJsonPath('can_create_statements', true);

        $user->update(['platform_id' => $newPlatform->id]);
        $user->syncRoles('User');

        $this->bearerGet($token->plainTextToken, '/testing/cached-token-user')
            ->assertOk()
            ->assertJsonPath('platform_id', $newPlatform->id)
            ->assertJsonPath('roles.0', 'User')
            ->assertJsonPath('can_create_statements', false);
    }

    private function bearerGet(string $token, string $uri = '/api/ping'): TestResponse
    {
        // A real request starts with a fresh guard; test requests share the application.
        Auth::forgetGuards();

        return $this->withToken($token)->getJson($uri);
    }

    private function tokenSelectQueries(): array
    {
        return array_filter(DB::getQueryLog(), static function (array $query): bool {
            $sql = strtolower(ltrim($query['query']));

            return str_starts_with($sql, 'select') && str_contains($sql, 'personal_access_tokens');
        });
    }
}
