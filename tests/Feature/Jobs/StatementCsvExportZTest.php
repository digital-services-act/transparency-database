<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementCsvExportZ;
use App\Models\Platform;
use App\Models\Statement;
use App\Services\DayArchiveService;
use App\Services\DayArchiveWorkspace;
use App\Services\PlatformQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class StatementCsvExportZTest extends TestCase
{
    use RefreshDatabase;

    private string $archive_workspace_path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->archive_workspace_path = sys_get_temp_dir().'/day_archive_export_workspace_'.uniqid();
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

    public function test_it_exports_only_the_requested_day_when_other_days_have_ids_inside_the_chunk_window(): void
    {
        $admin = $this->signInAsAdmin();
        $platform = Platform::nonDsa()->first();
        $other_platform = Platform::nonDsa()->where('id', '!=', $platform->id)->first();

        Statement::query()->forceDelete();

        $this->createStatementWithId(1010, '2030-01-02 00:00:00', 'TARGET_PLATFORM_FIRST', $platform->id, $admin->id);
        $this->createStatementWithId(1500, '2030-01-01 23:59:59', 'PREVIOUS_DAY_INSIDE_WINDOW', $platform->id, $admin->id);
        $this->createStatementWithId(2000, '2030-01-02 12:00:00', 'TARGET_OTHER_PLATFORM', $other_platform->id, $admin->id);
        $this->createStatementWithId(2500, '2030-01-03 00:00:00', 'NEXT_DAY_INSIDE_WINDOW', $platform->id, $admin->id);
        $this->createStatementWithId(4090, '2030-01-02 23:59:59', 'TARGET_PLATFORM_LAST', $platform->id, $admin->id);

        $day_archive_service = app(DayArchiveService::class);
        $day_archive_service->connection = config('database.default');

        $job = new StatementCsvExportZ('2030-01-02', '00000', 1010, 4090, true);
        $job->handle($day_archive_service, app(PlatformQueryService::class));

        $global_csv = $this->csvFromZip(
            $job->zipFilePathForSlugAndVersion('global', 'full'),
            $job->csvFilenameForSlugAndVersionAndSubpart('global', 'full', 0)
        );

        $this->assertCsvContainsPuid($global_csv, 'TARGET_PLATFORM_FIRST');
        $this->assertCsvContainsPuid($global_csv, 'TARGET_OTHER_PLATFORM');
        $this->assertCsvContainsPuid($global_csv, 'TARGET_PLATFORM_LAST');
        $this->assertCsvDoesNotContainPuid($global_csv, 'PREVIOUS_DAY_INSIDE_WINDOW');
        $this->assertCsvDoesNotContainPuid($global_csv, 'NEXT_DAY_INSIDE_WINDOW');
        $this->assertCount(4, explode("\n", trim($global_csv)));

        $platform_slug = $platform->slugifyName();
        $platform_csv = $this->csvFromZip(
            $job->zipFilePathForSlugAndVersion($platform_slug, 'full'),
            $job->csvFilenameForSlugAndVersionAndSubpart($platform_slug, 'full', 0)
        );

        $this->assertCsvContainsPuid($platform_csv, 'TARGET_PLATFORM_FIRST');
        $this->assertCsvContainsPuid($platform_csv, 'TARGET_PLATFORM_LAST');
        $this->assertCsvDoesNotContainPuid($platform_csv, 'TARGET_OTHER_PLATFORM');
        $this->assertCsvDoesNotContainPuid($platform_csv, 'PREVIOUS_DAY_INSIDE_WINDOW');
        $this->assertCsvDoesNotContainPuid($platform_csv, 'NEXT_DAY_INSIDE_WINDOW');
        $this->assertCount(3, explode("\n", trim($platform_csv)));
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

    private function csvFromZip(string $zip_path, string $csv_name): string
    {
        $this->assertFileExists($zip_path);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zip_path));

        $csv = $zip->getFromName($csv_name);
        $zip->close();

        $this->assertIsString($csv);

        return $csv;
    }

    private function assertCsvContainsPuid(string $csv, string $puid): void
    {
        $this->assertStringContainsString(','.$puid.',', $csv);
    }

    private function assertCsvDoesNotContainPuid(string $csv, string $puid): void
    {
        $this->assertStringNotContainsString(','.$puid.',', $csv);
    }
}
