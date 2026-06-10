<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementCsvExportCopyS3;
use App\Services\DayArchiveWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class StatementCsvExportCopyS3Test extends TestCase
{
    use RefreshDatabase;

    public function test_it_copies_zip_and_sha1_files_to_s3(): void
    {
        // Mock Storage facades
        $s3DiskMock = Mockery::mock();
        $s3DiskMock->shouldReceive('put')
            ->twice() // Once for zip, once for sha1
            ->with(Mockery::type('string'), Mockery::type('resource'), ['visibility' => 'private'])
            ->andReturn(true);

        Storage::shouldReceive('disk')->with('s3ds')->andReturn($s3DiskMock);

        // Create temporary directory for local path
        $tempDir = sys_get_temp_dir().'/test_csv_copy_'.uniqid().'/';
        File::makeDirectory($tempDir);

        // Create test files
        $zipFile = 'test-export.zip';
        $sha1File = 'test-export.zip.sha1';

        File::put($tempDir.$zipFile, 'fake zip content');
        File::put($tempDir.$sha1File, 'fake sha1 checksum');

        try {
            // Create and handle the job
            $job = new StatementCsvExportCopyS3($zipFile, $sha1File);
            $job->handle(new DayArchiveWorkspace($tempDir));
        } finally {
            // Clean up
            File::deleteDirectory($tempDir);
        }

        $this->assertTrue(true);
    }

    public function test_it_constructs_with_zip_and_sha1_filenames(): void
    {
        $zipFile = 'export-2025-09-16.zip';
        $sha1File = 'export-2025-09-16.zip.sha1';

        $job = new StatementCsvExportCopyS3($zipFile, $sha1File);

        $this->assertEquals($zipFile, $job->zip);
        $this->assertEquals($sha1File, $job->sha1);
    }
}
