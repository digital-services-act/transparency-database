<?php

namespace Tests\Feature\Services;

use App\Models\PersonalAccessToken;
use App\Models\Platform;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\TokenService;

class TokenServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_total_vlop_valid_tokens_correctly()
    {
        // Seed the test database with some data
        $this->seedTestData();

        $tokenService = new TokenService();
        $total_vlop_valid_tokens = $tokenService->getTotalVlopValidTokens();

        $this->assertEquals(2, $total_vlop_valid_tokens);
    }

    /** @test */
    public function it_calculates_total_non_vlop_valid_tokens_correctly()
    {
        // Seed the test database with some data
        $this->seedTestData();

        $tokenService = new TokenService();
        $total_non_vlop_valid_tokens = $tokenService->getTotalNonVlopValidTokens();

        $this->assertEquals(1, $total_non_vlop_valid_tokens);
    }

    private function seedTestData()
    {
        $platformA = Platform::factory()->create(['vlop' => 1, 'name' => 'Platform A']);
        $platformB = Platform::factory()->create(['vlop' => 0, 'name' => 'Platform B']);
        $platformC = Platform::getDsaPlatform();

        $user1 = User::factory()->create(['platform_id' => $platformA->id]);
        $user2 = User::factory()->create(['platform_id' => $platformA->id]);
        $user3 = User::factory()->create(['platform_id' => $platformB->id]);
        $user4 = User::factory()->create(['platform_id' => $platformC->id]);

        PersonalAccessToken::factory()->create(['tokenable_id' => $user1->id]);
        PersonalAccessToken::factory()->create(['tokenable_id' => $user2->id]);
        PersonalAccessToken::factory()->create(['tokenable_id' => $user3->id]);
        PersonalAccessToken::factory()->create(['tokenable_id' => $user4->id]);

        // Create second token for user 1 and user 3
        PersonalAccessToken::factory()->create(['tokenable_id' => $user1->id]);
        PersonalAccessToken::factory()->create(['tokenable_id' => $user3->id]);
    }
}
