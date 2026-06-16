<?php

namespace Tests\Feature\Factories;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatementFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_statement_for_user_with_non_dsa_platform(): void
    {
        $dsaPlatform = Platform::getDsaPlatform();
        $dsaUser = User::factory()->create([
            'platform_id' => $dsaPlatform->id,
        ]);

        $statement = Statement::factory()->create();
        $statement->load('user.platform', 'platform');

        $this->assertNotSame($dsaUser->id, $statement->user_id);
        $this->assertNotNull($statement->user);
        $this->assertNotNull($statement->user->platform);
        $this->assertSame($statement->user->platform_id, $statement->platform_id);
        $this->assertSame($statement->user->platform_id, $statement->platform->id);
        $this->assertNotSame(Platform::LABEL_DSA_TEAM, $statement->user->platform->name);
    }

    public function test_it_creates_eligible_user_when_none_exists(): void
    {
        User::query()->forceDelete();

        $statement = Statement::factory()->create();
        $statement->load('user.platform');

        $this->assertNotNull($statement->user);
        $this->assertNotNull($statement->user->platform);
        $this->assertSame($statement->user->platform_id, $statement->platform_id);
        $this->assertNotSame(Platform::LABEL_DSA_TEAM, $statement->user->platform->name);
    }

    public function test_it_creates_random_created_at_times(): void
    {
        $statements = Statement::factory()->count(25)->create();

        $times = $statements
            ->pluck('created_at')
            ->map(static fn ($createdAt): string => $createdAt->format('H:i:s'));

        $this->assertGreaterThan(1, $times->unique()->count());
        $this->assertTrue($times->contains(static fn (string $time): bool => $time !== '00:00:00'));

        foreach ($statements as $statement) {
            $this->assertSame(
                $statement->created_at->format('Y-m-d H:i:s'),
                $statement->updated_at->format('Y-m-d H:i:s'),
            );
        }
    }
}
