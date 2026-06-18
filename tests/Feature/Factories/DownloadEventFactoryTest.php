<?php

namespace Tests\Feature\Factories;

use App\Models\DownloadEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadEventFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_archive_download_event(): void
    {
        $event = DownloadEvent::factory()->create();

        $this->assertSame('archive', $event->download_kind);
        $this->assertContains($event->file_type, ['full', 'light']);
        $this->assertContains($event->route_name, [
            'dayarchive.download',
            'dayarchive.download.filename',
        ]);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $event->session_hash);
    }

    public function test_it_creates_checksum_and_aggregate_events(): void
    {
        $checksum = DownloadEvent::factory()->checksum()->create();
        $aggregate = DownloadEvent::factory()->aggregate()->create();

        $this->assertSame('checksum', $checksum->download_kind);
        $this->assertContains($checksum->file_type, ['sha1', 'sha1light']);
        $this->assertSame('aggregate', $aggregate->download_kind);
        $this->assertContains($aggregate->file_type, ['csv', 'json']);
        $this->assertSame('aggregates.download', $aggregate->route_name);
    }

    public function test_it_can_reuse_a_session_hash(): void
    {
        $sessionHash = hash('sha256', 'test-session');

        $events = DownloadEvent::factory()
            ->count(2)
            ->forSession($sessionHash)
            ->create();

        $this->assertSame([$sessionHash], $events->pluck('session_hash')->unique()->values()->all());
    }
}
