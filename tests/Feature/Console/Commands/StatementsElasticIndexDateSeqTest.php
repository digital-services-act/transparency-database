<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\StatementElasticSearchableChunk;
use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatementsElasticIndexDateSeqTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_skips_configured_id_gaps_when_queueing_indexing_jobs(): void
    {
        Queue::fake();

        $admin = $this->signInAsAdmin();
        $platform = Platform::nonDsa()->first();

        Statement::query()->forceDelete();

        $this->createStatementWithId(100, '2030-01-02 00:00:00', 'TARGET_FIRST', $platform->id, $admin->id);
        $this->createStatementWithId(2500000, '2030-01-02 23:59:59', 'TARGET_LAST', $platform->id, $admin->id);

        $this->artisan('statements:elastic-index-date-seq', [
            'date' => '2030-01-02',
            'chunk' => 2000,
            '--skip-id-range' => ['500000:2000000'],
        ])->assertSuccessful();

        Queue::assertPushed(StatementElasticSearchableChunk::class, 2);
        Queue::assertPushed(StatementElasticSearchableChunk::class, function (StatementElasticSearchableChunk $job): bool {
            return $job->min === 100
                && $job->max === 499999
                && $job->chunk === 2000;
        });
        Queue::assertPushed(StatementElasticSearchableChunk::class, function (StatementElasticSearchableChunk $job): bool {
            return $job->min === 2000001
                && $job->max === 2500000
                && $job->chunk === 2000;
        });
    }

    #[Test]
    public function it_supports_repeated_skip_id_ranges(): void
    {
        Queue::fake();

        $admin = $this->signInAsAdmin();
        $platform = Platform::nonDsa()->first();

        Statement::query()->forceDelete();

        $this->createStatementWithId(100, '2030-01-02 00:00:00', 'TARGET_FIRST', $platform->id, $admin->id);
        $this->createStatementWithId(4000000, '2030-01-02 23:59:59', 'TARGET_LAST', $platform->id, $admin->id);

        $this->artisan('statements:elastic-index-date-seq', [
            'date' => '2030-01-02',
            'chunk' => 2000,
            '--skip-id-range' => ['500000:1000000', '2000000:3000000'],
        ])->assertSuccessful();

        Queue::assertPushed(StatementElasticSearchableChunk::class, 3);
        Queue::assertPushed(StatementElasticSearchableChunk::class, function (StatementElasticSearchableChunk $job): bool {
            return $job->min === 100
                && $job->max === 499999
                && $job->chunk === 2000;
        });
        Queue::assertPushed(StatementElasticSearchableChunk::class, function (StatementElasticSearchableChunk $job): bool {
            return $job->min === 1000001
                && $job->max === 1999999
                && $job->chunk === 2000;
        });
        Queue::assertPushed(StatementElasticSearchableChunk::class, function (StatementElasticSearchableChunk $job): bool {
            return $job->min === 3000001
                && $job->max === 4000000
                && $job->chunk === 2000;
        });
    }

    private function createStatementWithId(int $id, string $created_at, string $puid, int $platform_id, int $user_id): Statement
    {
        return Statement::unguarded(fn () => Statement::factory()->create([
            'id' => $id,
            'created_at' => $created_at,
            'updated_at' => $created_at,
            'puid' => $puid,
            'platform_id' => $platform_id,
            'user_id' => $user_id,
        ]));
    }
}
