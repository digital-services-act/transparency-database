<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResyncPlatformHasTokensTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_sets_the_flag_for_users_with_non_expired_or_non_expiring_tokens(): void
    {
        $asOf = Carbon::parse('2026-06-18 12:00:00');
        Carbon::setTestNow($asOf);

        $neverExpiresPlatform = Platform::factory()->create(['has_tokens' => false]);
        $futureExpiryPlatform = Platform::factory()->create(['has_tokens' => false]);
        $expiresNowPlatform = Platform::factory()->create(['has_tokens' => false]);

        User::factory()->create(['platform_id' => $neverExpiresPlatform->id])
            ->createToken('non-expiring-token');

        $futureToken = User::factory()->create(['platform_id' => $futureExpiryPlatform->id])
            ->createToken('future-expiry-token')->accessToken;

        $expiresNowToken = User::factory()->create(['platform_id' => $expiresNowPlatform->id])
            ->createToken('expires-now-token')->accessToken;

        DB::table('personal_access_tokens')->where('id', $futureToken->id)
            ->update(['expires_at' => $asOf->copy()->addSecond()]);
        DB::table('personal_access_tokens')->where('id', $expiresNowToken->id)
            ->update(['expires_at' => $asOf]);

        $this->artisan('platforms:resync-has-tokens')
            ->assertSuccessful();

        $this->assertDatabaseHas('platforms', ['id' => $neverExpiresPlatform->id, 'has_tokens' => true]);
        $this->assertDatabaseHas('platforms', ['id' => $futureExpiryPlatform->id, 'has_tokens' => true]);
        $this->assertDatabaseHas('platforms', ['id' => $expiresNowPlatform->id, 'has_tokens' => true]);
    }

    public function test_it_clears_the_flag_when_a_platform_has_no_valid_user_tokens(): void
    {
        $asOf = Carbon::parse('2026-06-18 12:00:00');
        Carbon::setTestNow($asOf);

        $expiredTokenPlatform = Platform::factory()->create(['has_tokens' => true]);
        $noTokenPlatform = Platform::factory()->create(['has_tokens' => true]);

        $expiredToken = User::factory()->create(['platform_id' => $expiredTokenPlatform->id])
            ->createToken('expired-token')->accessToken;

        DB::table('personal_access_tokens')->where('id', $expiredToken->id)
            ->update(['expires_at' => $asOf->copy()->subSecond()]);

        $this->artisan('platforms:resync-has-tokens')
            ->assertSuccessful();

        $this->assertDatabaseHas('platforms', ['id' => $expiredTokenPlatform->id, 'has_tokens' => false]);
        $this->assertDatabaseHas('platforms', ['id' => $noTokenPlatform->id, 'has_tokens' => false]);
    }
}
