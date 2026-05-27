<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Platform;
use App\Models\PlatformPuid;
use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatementsPruneOldTest extends TestCase
{
    use RefreshDatabase;

    #[\Override]
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function it_prunes_old_statements_and_platform_puids_in_batches(): void
    {
        Carbon::setTestNow('2026-05-27 12:00:00');
        $this->clearPrunedTables();

        $platformId = Platform::nonDsa()->first()->id;

        Statement::factory()->create(['id' => 100000000001, 'created_at' => '2025-11-27 23:59:59']);
        Statement::factory()->create(['id' => 100000000002, 'created_at' => '2025-11-27 23:59:59']);
        Statement::factory()->create(['id' => 100000000003, 'created_at' => '2025-11-28 00:00:00']);
        Statement::factory()->create(['id' => 100000000004, 'created_at' => '2025-11-29 00:00:00']);

        PlatformPuid::factory()->create([
            'platform_id' => $platformId,
            'puid' => 'old-puid-1',
            'created_at' => '2025-11-27 23:59:59',
        ]);
        PlatformPuid::factory()->create([
            'platform_id' => $platformId,
            'puid' => 'old-puid-2',
            'created_at' => '2025-11-27 23:59:59',
        ]);
        PlatformPuid::factory()->create([
            'platform_id' => $platformId,
            'puid' => 'cutoff-puid',
            'created_at' => '2025-11-28 00:00:00',
        ]);
        PlatformPuid::factory()->create([
            'platform_id' => $platformId,
            'puid' => 'new-puid',
            'created_at' => '2025-11-29 00:00:00',
        ]);

        $this->artisan('statements:prune-old', [
            '--batch' => 1,
            '--skip-elastic' => true,
        ])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('statements_beta', ['id' => 100000000001]);
        $this->assertDatabaseMissing('statements_beta', ['id' => 100000000002]);
        $this->assertDatabaseHas('statements_beta', ['id' => 100000000003]);
        $this->assertDatabaseHas('statements_beta', ['id' => 100000000004]);

        $this->assertDatabaseMissing('platform_puids', ['puid' => 'old-puid-1']);
        $this->assertDatabaseMissing('platform_puids', ['puid' => 'old-puid-2']);
        $this->assertDatabaseHas('platform_puids', ['puid' => 'cutoff-puid']);
        $this->assertDatabaseHas('platform_puids', ['puid' => 'new-puid']);
    }

    #[Test]
    public function it_can_limit_the_number_of_batches_per_table(): void
    {
        Carbon::setTestNow('2026-05-27 12:00:00');
        $this->clearPrunedTables();

        $platformId = Platform::nonDsa()->first()->id;

        Statement::factory()->create(['id' => 100000000001, 'created_at' => '2025-11-27 23:59:59']);
        Statement::factory()->create(['id' => 100000000002, 'created_at' => '2025-11-27 23:59:59']);
        Statement::factory()->create(['id' => 100000000003, 'created_at' => '2025-11-27 23:59:59']);

        PlatformPuid::factory()->create([
            'platform_id' => $platformId,
            'puid' => 'old-puid-1',
            'created_at' => '2025-11-27 23:59:59',
        ]);
        PlatformPuid::factory()->create([
            'platform_id' => $platformId,
            'puid' => 'old-puid-2',
            'created_at' => '2025-11-27 23:59:59',
        ]);
        PlatformPuid::factory()->create([
            'platform_id' => $platformId,
            'puid' => 'old-puid-3',
            'created_at' => '2025-11-27 23:59:59',
        ]);

        $this->artisan('statements:prune-old', [
            '--batch' => 2,
            '--max-batches' => 1,
            '--skip-elastic' => true,
        ])->assertExitCode(0);

        $this->assertSame(1, DB::table('statements_beta')->where('created_at', '<', '2025-11-28 00:00:00')->count());
        $this->assertSame(1, DB::table('platform_puids')->where('created_at', '<', '2025-11-28 00:00:00')->count());
    }

    #[Test]
    public function it_starts_an_async_elasticsearch_prune_before_database_batches(): void
    {
        Carbon::setTestNow('2026-05-27 12:00:00');
        $this->clearPrunedTables();

        $this->mock(StatementElasticSearchService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->once()->andReturnTrue();
            $mock->shouldReceive('deleteStatementsBeforeDate')
                ->once()
                ->with(
                    Mockery::on(fn (Carbon $cutoff): bool => $cutoff->equalTo(Carbon::parse('2025-11-28 00:00:00'))),
                    false
                )
                ->andReturn(['task' => 'node-1:12345']);
        });

        $this->artisan('statements:prune-old', [
            '--batch' => 1,
            '--max-batches' => 1,
        ])
            ->expectsOutput('Elasticsearch prune task started: node-1:12345')
            ->assertExitCode(0);
    }

    #[Test]
    public function it_fails_without_deleting_database_rows_when_elasticsearch_is_required_but_not_configured(): void
    {
        Carbon::setTestNow('2026-05-27 12:00:00');
        $this->clearPrunedTables();

        Statement::factory()->create(['id' => 100000000001, 'created_at' => '2025-11-27 23:59:59']);

        $this->mock(StatementElasticSearchService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('isConfigured')->once()->andReturnFalse();
            $mock->shouldNotReceive('deleteStatementsBeforeDate');
        });

        $this->artisan('statements:prune-old', ['--batch' => 1])
            ->expectsOutput('Elasticsearch prune failed: Elasticsearch is not configured. Use --skip-elastic to prune only the database tables.')
            ->assertExitCode(1);

        $this->assertDatabaseHas('statements_beta', ['id' => 100000000001]);
    }

    #[Test]
    public function it_rejects_invalid_batch_options(): void
    {
        $this->artisan('statements:prune-old', ['--batch' => 0])
            ->assertExitCode(1)
            ->expectsOutput('The --batch option must be greater than zero.');
    }

    private function clearPrunedTables(): void
    {
        DB::table('statements_beta')->delete();
        DB::table('platform_puids')->delete();
    }
}
