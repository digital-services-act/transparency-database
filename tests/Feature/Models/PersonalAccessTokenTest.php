<?php

namespace Tests\Feature\Models;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PersonalAccessTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_has_correct_ttl_and_interval_defaults()
    {
        $this->assertEquals(300, PersonalAccessToken::$ttl);
        $this->assertEquals(3600, PersonalAccessToken::$interval);
    }

    public function test_it_caches_last_used_at_updates()
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

    public function test_it_respects_configured_cache_interval()
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

    public function test_it_clears_cache_on_token_deletion()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;

        // Set some cache values
        $lastUsedCacheKey = sprintf('personal-access-token:%s:last_used_at', $token->id);
        $tokenableCacheKey = sprintf('personal-access-token:%s:tokenable', $token->id);
        $tokenCacheKey = 'personal-access-token:'.$token->id;

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

    public function test_it_logs_critical_error_on_cache_failure()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;

        // Force Cache to throw an exception
        Cache::shouldReceive('remember')
            ->once()
            ->andThrow(new \Exception('Cache error'));

        // Mock Log facade
        Log::shouldReceive('critical')
            ->once()
            ->with('Critical Personal Access Token Error', \Mockery::hasKey('exception'));

        // Update token
        $token->last_used_at = now();
        $token->save();
    }

    public function test_it_updates_database_when_caching_last_used_at()
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
            'last_used_at' => $newLastUsedAt,
        ]);
    }

    public function test_repeated_token_lookups_only_query_the_database_once(): void
    {
        $token = User::factory()->create()->createToken('test-token');

        DB::enableQueryLog();
        DB::flushQueryLog();

        $first = PersonalAccessToken::findToken($token->plainTextToken);
        $second = PersonalAccessToken::findToken($token->plainTextToken);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(1, $queries);
        $this->assertTrue($first->is($second));
        $this->assertNotSame($first, $second);
    }

    public function test_it_uses_the_configured_token_cache_store(): void
    {
        config([
            'cache.stores.tokens' => ['driver' => 'array', 'serialize' => true],
            'sanctum.cache.store' => 'tokens',
        ]);
        $token = User::factory()->create()->createToken('test-token');

        PersonalAccessToken::findToken($token->plainTextToken);

        $key = 'personal-access-token:'.$token->accessToken->id;
        $this->assertNull(Cache::get($key));
        $this->assertIsArray(Cache::store('tokens')->get($key));

        $token->accessToken->delete();

        $this->assertNull(Cache::store('tokens')->get($key));
        $this->assertNull(PersonalAccessToken::findToken($token->plainTextToken));
    }

    public function test_cache_hits_do_not_extend_the_five_minute_lifetime(): void
    {
        $this->freezeTime();
        $token = User::factory()->create()->createToken('original-name');
        PersonalAccessToken::findToken($token->plainTextToken);

        // Simulate a database change that bypasses model invalidation.
        DB::table('personal_access_tokens')->where('id', $token->accessToken->id)
            ->update(['name' => 'updated-name']);

        $this->travel(299)->seconds();
        $this->assertSame('original-name', PersonalAccessToken::findToken($token->plainTextToken)->name);

        $this->travel(1)->seconds();
        $this->assertSame('updated-name', PersonalAccessToken::findToken($token->plainTextToken)->name);
    }

    public function test_it_verifies_the_secret_on_cold_and_warm_lookups(): void
    {
        $token = User::factory()->create()->createToken('test-token');
        $incorrectToken = $token->accessToken->id.'|incorrect-secret';
        $key = 'personal-access-token:'.$token->accessToken->id;

        $this->assertNull(PersonalAccessToken::findToken($incorrectToken));
        $this->assertFalse(Cache::has($key));

        $this->assertNotNull(PersonalAccessToken::findToken($token->plainTextToken));
        $this->assertNull(PersonalAccessToken::findToken($incorrectToken));
        $this->assertNull(PersonalAccessToken::findToken('invalid|secret'));
        $this->assertNull(PersonalAccessToken::findToken($token->accessToken->id.'|'));
        $this->assertNull(PersonalAccessToken::findToken('unknown-secret'));
        $this->assertFalse(Cache::has('personal-access-token:hash:'.hash('sha256', 'unknown-secret')));
    }

    public function test_deletion_invalidates_both_token_formats_and_id_aliases(): void
    {
        $token = User::factory()->create()->createToken('test-token');
        [, $secret] = explode('|', $token->plainTextToken, 2);
        $aliases = [$token->plainTextToken, '000'.$token->plainTextToken, $secret];

        foreach ($aliases as $alias) {
            $this->assertTrue(PersonalAccessToken::findToken($alias)->is($token->accessToken));
        }

        $token->accessToken->delete();

        foreach ($aliases as $alias) {
            $this->assertNull(PersonalAccessToken::findToken($alias));
        }
    }

    public function test_cached_attributes_do_not_retain_user_relations_or_model_changes(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('original-name');
        $cachedToken = PersonalAccessToken::findToken($token->plainTextToken);
        $this->assertSame($user->email, $cachedToken->tokenable->email);
        $cachedToken->name = 'unsaved-name';

        $user->update(['email' => 'updated@example.com']);
        $nextToken = PersonalAccessToken::findToken($token->plainTextToken);

        $this->assertFalse($nextToken->relationLoaded('tokenable'));
        $this->assertSame('original-name', $nextToken->name);
        $this->assertSame('updated@example.com', $nextToken->tokenable->email);
    }

    public function test_security_updates_are_saved_and_invalidate_both_formats_even_after_usage_tracking(): void
    {
        $this->freezeTime();
        $token = User::factory()->create()->createToken('test-token');
        [, $secret] = explode('|', $token->plainTextToken, 2);
        $model = $token->accessToken;
        $model->last_used_at = now();
        $model->save();

        PersonalAccessToken::findToken($token->plainTextToken);
        PersonalAccessToken::findToken($secret);

        $model->abilities = ['read'];
        $model->expires_at = now()->addMinute();
        $this->assertTrue($model->save());

        foreach ([$token->plainTextToken, $secret] as $plainTextToken) {
            $updated = PersonalAccessToken::findToken($plainTextToken);
            $this->assertTrue($updated->can('read'));
            $this->assertFalse($updated->can('write'));
            $this->assertSame(now()->addMinute()->toDateTimeString(), $updated->expires_at->toDateTimeString());
        }

        $this->travel(60)->seconds();
        $this->assertNull(Cache::get('personal-access-token:'.$model->id));
        $this->assertNull(Cache::get('personal-access-token:hash:'.$model->token));
    }

    public function test_rotating_the_secret_invalidates_the_original_hash_cache(): void
    {
        $token = User::factory()->create()->createToken('test-token');
        [, $secret] = explode('|', $token->plainTextToken, 2);
        PersonalAccessToken::findToken($token->plainTextToken);
        PersonalAccessToken::findToken($secret);

        $token->accessToken->update(['token' => hash('sha256', 'replacement-secret')]);

        $this->assertNull(PersonalAccessToken::findToken($token->plainTextToken));
        $this->assertNull(PersonalAccessToken::findToken($secret));
        $this->assertNotNull(PersonalAccessToken::findToken($token->accessToken->id.'|replacement-secret'));
        $this->assertNotNull(PersonalAccessToken::findToken('replacement-secret'));
    }

    public function test_it_does_not_cache_beyond_global_token_expiration(): void
    {
        $this->freezeTime();
        config(['sanctum.expiration' => 1]);
        $token = User::factory()->create()->createToken('test-token');
        PersonalAccessToken::findToken($token->plainTextToken);

        $this->travel(60)->seconds();

        $this->assertNull(Cache::get('personal-access-token:'.$token->accessToken->id));
    }

    public function test_cache_failure_does_not_authenticate_a_token(): void
    {
        $token = User::factory()->create()->createToken('test-token');
        Cache::shouldReceive('store')->once()->with('array')->andThrow(new \RuntimeException('Cache unavailable'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cache unavailable');

        PersonalAccessToken::findToken($token->plainTextToken);
    }
}
