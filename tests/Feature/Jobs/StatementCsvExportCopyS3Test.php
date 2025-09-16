<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementCsvExportCopyS3;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class StatementCsvExportCopyS3Test extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_copies_zip_and_sha1_files_to_s3(): void
    {
        // Mock Storage facades
        $s3DiskMock = Mockery::mock();
        $s3DiskMock->shouldReceive('put')
            ->twice() // Once for zip, once for sha1
            ->with(Mockery::type('string'), Mockery::type('resource'), ['visibility' => 'public'])
            ->andReturn(true);

        Storage::shouldReceive('disk')->with('s3ds')->andReturn($s3DiskMock);
        
        // Create temporary directory for local path
        $tempDir = sys_get_temp_dir() . '/test_csv_copy_' . time() . '/';
        mkdir($tempDir, 0755, true);
        Storage::shouldReceive('path')->with('')->andReturn($tempDir);

        // Create test files
        $zipFile = 'test-export.zip';
        $sha1File = 'test-export.zip.sha1';
        
        file_put_contents($tempDir . $zipFile, 'fake zip content');
        file_put_contents($tempDir . $sha1File, 'fake sha1 checksum');

        // Create and handle the job
        $job = new StatementCsvExportCopyS3($zipFile, $sha1File);
        $job->handle();

        // Clean up
        unlink($tempDir . $zipFile);
        unlink($tempDir . $sha1File);
        rmdir($tempDir);

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_constructs_with_zip_and_sha1_filenames(): void
    {
        $zipFile = 'export-2025-09-16.zip';
        $sha1File = 'export-2025-09-16.zip.sha1';

        $job = new StatementCsvExportCopyS3($zipFile, $sha1File);

        $this->assertEquals($zipFile, $job->zip);
        $this->assertEquals($sha1File, $job->sha1);
    }
}