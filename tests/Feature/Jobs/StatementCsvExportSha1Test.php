<?php

namespace Tests\Feature\Jobs;

use App\Jobs\StatementCsvExportSha1;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StatementCsvExportSha1Test extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_runs_without_errors(): void
    {
        // Create a temporary directory and file for testing
        $tempDir = sys_get_temp_dir() . '/test_storage';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $zipFileName = 'sor-test-platform-2025-09-05-v1.0.zip';
        $tempZipFile = $tempDir . '/' . $zipFileName;
        
        // Create a dummy zip file
        file_put_contents($tempZipFile, 'dummy zip content');

        // Mock Storage facade
        Storage::shouldReceive('path')->with('')->andReturn($tempDir . '/');
        Storage::shouldReceive('put')->once()->with(
            'sor-test-platform-2025-09-05-v1.0.zip.sha1',
            \Mockery::type('string')
        );

        $job = new StatementCsvExportSha1('2025-09-05', 'test-platform', 'v1.0');
        
        // This should run without throwing exceptions
        $job->handle();

        // Clean up
        unlink($tempZipFile);
        rmdir($tempDir);

        // The test passes if no exceptions were thrown
        $this->assertTrue(true);
    }
}