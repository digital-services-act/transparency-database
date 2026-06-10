<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\StatementCsvExportZ;
use App\Models\DayArchive;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\DayArchiveWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StatementsDayArchiveZTest extends TestCase
{
    use RefreshDatabase;

    private string $archive_workspace_path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->archive_workspace_path = sys_get_temp_dir().'/day_archive_command_workspace_'.uniqid();
        File::makeDirectory($this->archive_workspace_path);
        $this->app->instance(DayArchiveWorkspace::class, new DayArchiveWorkspace($this->archive_workspace_path));
    }

    protected function tearDown(): void
    {
        if (isset($this->archive_workspace_path) && File::isDirectory($this->archive_workspace_path)) {
            File::deleteDirectory($this->archive_workspace_path);
        }

        parent::tearDown();
    }

    public function test_it_skips_configured_id_gaps_when_queueing_csv_export_jobs(): void
    {
        Bus::fake();

        $admin = $this->signInAsAdmin();
        $platform = Platform::nonDsa()->first();

        Statement::query()->forceDelete();

        $this->createStatementWithId(100, '2030-01-02 00:00:00', 'TARGET_FIRST', $platform->id, $admin->id);
        $this->createStatementWithId(2500000, '2030-01-02 23:59:59', 'TARGET_LAST', $platform->id, $admin->id);

        $this->artisan('statements:day-archive-z', [
            'date' => '2030-01-02',
            '--skip-id-range' => ['500000:2000000'],
        ])->assertSuccessful();

        $batches = Bus::dispatchedBatches();

        $this->assertCount(1, $batches);
        $this->assertCount(2, $batches[0]->jobs);

        /** @var StatementCsvExportZ $firstJob */
        $firstJob = $batches[0]->jobs[0];
        /** @var StatementCsvExportZ $secondJob */
        $secondJob = $batches[0]->jobs[1];

        $this->assertSame('00000', $firstJob->part);
        $this->assertSame(100, $firstJob->start_id);
        $this->assertSame(499999, $firstJob->end_id);

        $this->assertSame('00001', $secondJob->part);
        $this->assertSame(2000001, $secondJob->start_id);
        $this->assertSame(2500000, $secondJob->end_id);
    }

    public function test_it_deletes_existing_day_archives_for_the_target_date_before_queueing_jobs(): void
    {
        Bus::fake();

        $admin = $this->signInAsAdmin();
        $platform = Platform::nonDsa()->first();

        Statement::query()->forceDelete();

        $this->createStatementWithId(100, '2030-01-02 00:00:00', 'TARGET_FIRST', $platform->id, $admin->id);
        $this->createStatementWithId(200, '2030-01-02 23:59:59', 'TARGET_LAST', $platform->id, $admin->id);

        $globalArchive = DayArchive::factory()->completed()->global()->create([
            'date' => '2030-01-02',
        ]);
        $platformArchive = DayArchive::factory()->completed()->forPlatform($platform)->create([
            'date' => '2030-01-02',
        ]);
        $otherDateArchive = DayArchive::factory()->completed()->forPlatform($platform)->create([
            'date' => '2030-01-03',
        ]);

        $this->artisan('statements:day-archive-z', [
            'date' => '2030-01-02',
        ])->assertSuccessful();

        $this->assertDatabaseMissing('day_archives', ['id' => $globalArchive->id]);
        $this->assertDatabaseMissing('day_archives', ['id' => $platformArchive->id]);
        $this->assertDatabaseHas('day_archives', ['id' => $otherDateArchive->id]);
    }

    public function test_it_supports_repeated_skip_id_ranges(): void
    {
        Bus::fake();

        $admin = $this->signInAsAdmin();
        $platform = Platform::nonDsa()->first();

        Statement::query()->forceDelete();

        $this->createStatementWithId(100, '2030-01-02 00:00:00', 'TARGET_FIRST', $platform->id, $admin->id);
        $this->createStatementWithId(4000000, '2030-01-02 23:59:59', 'TARGET_LAST', $platform->id, $admin->id);

        $this->artisan('statements:day-archive-z', [
            'date' => '2030-01-02',
            '--skip-id-range' => ['500000:1000000', '2000000:3000000'],
        ])->assertSuccessful();

        $batches = Bus::dispatchedBatches();

        $this->assertCount(1, $batches);
        $this->assertCount(3, $batches[0]->jobs);

        $this->assertSame([100, 499999], [
            $batches[0]->jobs[0]->start_id,
            $batches[0]->jobs[0]->end_id,
        ]);
        $this->assertSame([1000001, 1999999], [
            $batches[0]->jobs[1]->start_id,
            $batches[0]->jobs[1]->end_id,
        ]);
        $this->assertSame([3000001, 4000000], [
            $batches[0]->jobs[2]->start_id,
            $batches[0]->jobs[2]->end_id,
        ]);
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
