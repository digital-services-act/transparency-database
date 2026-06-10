<?php

namespace Tests\Feature\Services;

use App\Services\DayArchiveWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DayArchiveWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_bucket_data_path_when_it_exists(): void
    {
        $bucket_data_path = sys_get_temp_dir().'/day_archive_bucket_data_'.uniqid();
        File::makeDirectory($bucket_data_path);

        try {
            $workspace = new DayArchiveWorkspace($bucket_data_path);

            $this->assertSame($bucket_data_path.DIRECTORY_SEPARATOR, $workspace->path());
            $this->assertSame($bucket_data_path.DIRECTORY_SEPARATOR.'archive.zip', $workspace->path('archive.zip'));

            $workspace->put('archive.zip', 'archive');

            $this->assertSame('archive', File::get($bucket_data_path.'/archive.zip'));
        } finally {
            File::deleteDirectory($bucket_data_path);
        }
    }

    public function test_it_falls_back_to_local_storage_when_bucket_data_path_is_missing(): void
    {
        Storage::fake('local');

        $workspace = new DayArchiveWorkspace(sys_get_temp_dir().'/missing_day_archive_bucket_data_'.uniqid());
        $expected_path = rtrim(Storage::path(''), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'archive.zip';

        $this->assertSame($expected_path, $workspace->path('archive.zip'));

        $workspace->put('archive.zip', 'archive');

        Storage::disk('local')->assertExists('archive.zip');
    }

    public function test_it_deletes_matching_date_files_from_the_active_workspace(): void
    {
        $bucket_data_path = sys_get_temp_dir().'/day_archive_cleanup_'.uniqid();
        File::makeDirectory($bucket_data_path);

        try {
            File::put($bucket_data_path.'/sor-global-2030-01-02-full.zip', 'delete');
            File::put($bucket_data_path.'/sor-global-2030-01-03-full.zip', 'keep');

            (new DayArchiveWorkspace($bucket_data_path))->deleteFilesForDate('2030-01-02');

            $this->assertFileDoesNotExist($bucket_data_path.'/sor-global-2030-01-02-full.zip');
            $this->assertFileExists($bucket_data_path.'/sor-global-2030-01-03-full.zip');
        } finally {
            File::deleteDirectory($bucket_data_path);
        }
    }
}
