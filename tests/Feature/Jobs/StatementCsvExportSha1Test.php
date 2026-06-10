<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementCsvExportSha1;
use App\Services\DayArchiveWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StatementCsvExportSha1Test extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_without_errors(): void
    {
        // Create a temporary directory and file for testing
        $tempDir = sys_get_temp_dir().'/test_storage_'.uniqid();
        File::makeDirectory($tempDir);

        $zipFileName = 'sor-test-platform-2025-09-05-v1.0.zip';
        $tempZipFile = $tempDir.'/'.$zipFileName;

        // Create a dummy zip file
        File::put($tempZipFile, 'dummy zip content');

        $job = new StatementCsvExportSha1('2025-09-05', 'test-platform', 'v1.0');
        $workspace = new DayArchiveWorkspace($tempDir);

        try {
            // This should run without throwing exceptions
            $job->handle($workspace);

            $this->assertSame(
                sha1_file($tempZipFile).'  '.$zipFileName,
                File::get($tempDir.'/'.$zipFileName.'.sha1')
            );
        } finally {
            File::deleteDirectory($tempDir);
        }
    }
}
