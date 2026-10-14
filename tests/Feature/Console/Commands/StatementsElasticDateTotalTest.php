<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementElasticStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class StatementsElasticDateTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_legacy_second_by_second_id_bounds_when_requested(): void
    {
        $admin = $this->signInAsAdmin();
        $platform = Platform::nonDsa()->first();

        Statement::query()->forceDelete();

        $this->createStatementWithId(1000, '2030-01-02 00:00:05', 'LEGACY_FIRST', $platform->id, $admin->id);
        $this->createStatementWithId(900, '2030-01-02 00:00:10', 'LATER_LOWER_ID', $platform->id, $admin->id);
        $this->createStatementWithId(4000, '2030-01-02 23:59:50', 'EARLIER_HIGHER_ID', $platform->id, $admin->id);
        $this->createStatementWithId(3000, '2030-01-02 23:59:55', 'LEGACY_LAST', $platform->id, $admin->id);

        $this->mock(StatementElasticStatsService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('totalForDate')->once()->andReturn(2000);
            $mock->shouldReceive('totalsForPlatformsDate')->once()->andReturn([]);
            $mock->shouldReceive('methodsByPlatformsDate')->once()->andReturn([]);
        });

        $this->artisan('statements:elastic-date-total', [
            'date' => '2030-01-02',
            '--legacy-second-by-second' => true,
        ])
            ->expectsOutput('First ID: 1000')
            ->expectsOutput('Last ID: 3000')
            ->assertSuccessful();
    }

    private function createStatementWithId(int $id, string $createdAt, string $puid, int $platformId, int $userId): Statement
    {
        return Statement::unguarded(fn () => Statement::factory()->create([
            'id' => $id,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'puid' => $puid,
            'platform_id' => $platformId,
            'user_id' => $userId,
        ]));
    }
}
