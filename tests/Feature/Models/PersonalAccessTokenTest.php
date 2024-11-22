<?php

namespace Tests\Feature\Models;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /** @test */
    public function it_has_correct_ttl_and_interval_defaults()
    {
        $this->assertEquals(3600, PersonalAccessToken::$ttl);
        $this->assertEquals(3600, PersonalAccessToken::$interval);
    }

    /** @test */
    public function it_caches_last_used_at_updates()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;

        // Update last_used_at
        $token->last_used_at = now();
        $token->save();

        // Check if cache was set
        $cacheKey = sprintf('personal-access-token:%s:last_used_at', $token->id);
        $this->assertNotNull(Cache::get($cacheKey));
    }

    /** @test */
    public function it_respects_configured_cache_interval()
    {
        $customInterval = 7200;
        config(['sanctum.cache.update_last_used_at_interval' => $customInterval]);

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;

        // Update last_used_at
        $token->last_used_at = now();
        $token->save();

        // Check if cache exists
        $cacheKey = sprintf('personal-access-token:%s:last_used_at', $token->id);
        $this->assertNotNull(Cache::get($cacheKey));

        // Verify that the cache interval is respected by trying to access after half the interval
        $this->travel($customInterval / 2)->seconds();
        $this->assertNotNull(Cache::get($cacheKey));

        // Verify that the cache is cleared after the full interval
        $this->travel($customInterval / 2)->seconds();
        $this->assertNull(Cache::get($cacheKey));
    }

    /** @test */
    public function it_clears_cache_on_token_deletion()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;

        // Set some cache values
        $lastUsedCacheKey = sprintf('personal-access-token:%s:last_used_at', $token->id);
        $tokenableCacheKey = sprintf('personal-access-token:%s:tokenable', $token->id);
        $tokenCacheKey = 'personal-access-token:' . $token->id;

        Cache::put($lastUsedCacheKey, now(), 3600);
        Cache::put($tokenableCacheKey, $user, 3600);
        Cache::put($tokenCacheKey, $token, 3600);

        // Delete token
        $token->delete();

        // Verify cache was cleared
        $this->assertNull(Cache::get($lastUsedCacheKey));
        $this->assertNull(Cache::get($tokenableCacheKey));
        $this->assertNull(Cache::get($tokenCacheKey));
    }

    /** @test */
    public function it_logs_critical_error_on_cache_failure()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;

        // Force Cache to throw an exception
        Cache::shouldReceive('remember')
            ->once()
            ->andThrow(new \Exception('Cache error'));

        // Mock Log facade
        \Illuminate\Support\Facades\Log::shouldReceive('critical')
            ->once()
            ->with('Critical Personal Access Token Error', \Mockery::hasKey('exception'));

        // Update token
        $token->last_used_at = now();
        $token->save();
    }

    /** @test */
    public function it_updates_database_when_caching_last_used_at()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;
        $newLastUsedAt = now();

        // Update last_used_at
        $token->last_used_at = $newLastUsedAt;
        $token->save();

        // Verify database was updated
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->id,
            'last_used_at' => $newLastUsedAt
        ]);
    }
}
