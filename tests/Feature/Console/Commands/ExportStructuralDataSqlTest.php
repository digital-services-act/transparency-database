<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ExportStructuralDataSqlTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_encrypted_zip_and_uploads_it_to_s3ds(): void
    {
        Storage::fake('s3ds');

        config()->set('filesystems.disks.s3ds.bucket', 'structural-export-bucket');
        config()->set('filesystems.disks.s3ds.region', 'eu-west-1');

        $sqlPath = storage_path('framework/testing/structural-data.sql');
        $zipPath = $sqlPath.'.zip';
        $s3Path = 'exports/structural-data.sql.zip';

        File::delete([$sqlPath, $zipPath]);

        $this->artisan('db:export-structural-sql', [
            'path' => $sqlPath,
            '--force' => true,
            '--s3-path' => $s3Path,
        ])
            ->expectsQuestion('ZIP password', 'secret-pass')
            ->expectsQuestion('Confirm ZIP password', 'secret-pass')
            ->expectsOutput('Download URL: https://structural-export-bucket.s3.eu-west-1.amazonaws.com/exports/structural-data.sql.zip')
            ->assertExitCode(Command::SUCCESS);

        $this->assertFileExists($sqlPath);
        $this->assertFileExists($zipPath);
        Storage::disk('s3ds')->assertExists($s3Path);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zipPath));
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('structural-data.sql', $zip->statIndex(0)['name']);
        $this->assertFalse($zip->getFromName('structural-data.sql'));

        $this->assertTrue($zip->setPassword('secret-pass'));
        $this->assertStringContainsString(
            '-- Structural data export for transparency-database',
            $zip->getFromName('structural-data.sql')
        );

        $zip->close();
    }
}
