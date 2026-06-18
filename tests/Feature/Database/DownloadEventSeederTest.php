<?php

namespace Tests\Feature\Database;

use App\Models\DownloadEvent;
use Database\Seeders\DownloadEventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DownloadEventSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_seeds_realistic_recent_download_activity(): void
    {
        Carbon::setTestNow('2026-06-18 12:00:00');

        DownloadEvent::factory()->create([
            'filename' => 'real-local-download.zip',
        ]);

        $this->seed(DownloadEventSeeder::class);
        $seededCount = DownloadEvent::query()
            ->where('filename', 'like', 'demo-%')
            ->count();

        $this->assertGreaterThan(250, $seededCount);
        $this->assertDatabaseHas('download_events', ['download_kind' => 'archive']);
        $this->assertDatabaseHas('download_events', ['download_kind' => 'checksum']);
        $this->assertDatabaseHas('download_events', ['download_kind' => 'aggregate']);
        $this->assertDatabaseHas('download_events', ['route_name' => 'dayarchive.download']);
        $this->assertDatabaseHas('download_events', ['route_name' => 'dayarchive.download.filename']);
        $this->assertDatabaseHas('download_events', ['route_name' => 'dayarchive.download.filename.sha1']);
        $this->assertDatabaseHas('download_events', ['route_name' => 'aggregates.download']);
        $this->assertDatabaseHas('download_events', ['filename' => 'real-local-download.zip']);

        $this->seed(DownloadEventSeeder::class);

        $this->assertSame(
            $seededCount,
            DownloadEvent::query()->where('filename', 'like', 'demo-%')->count()
        );
        $this->assertSame(1, DownloadEvent::query()->where('filename', 'real-local-download.zip')->count());
    }
}
