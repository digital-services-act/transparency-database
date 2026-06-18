<?php

namespace Tests\Feature\Console\Commands;

use App\Models\DownloadEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PruneDownloadActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_deletes_only_events_older_than_the_configured_retention_period(): void
    {
        $now = Carbon::parse('2026-06-18 12:00:00');
        Carbon::setTestNow($now);
        config()->set('downloads.activity_retention_days', 180);

        $expired = $this->downloadEventAt($now->copy()->subDays(181));
        $retained = $this->downloadEventAt($now->copy()->subDays(180));

        $this->artisan('downloads:prune-activity')
            ->expectsOutput('Deleted 1 expired download activity event(s).')
            ->assertSuccessful();

        $this->assertDatabaseMissing('download_events', ['id' => $expired->id]);
        $this->assertDatabaseHas('download_events', ['id' => $retained->id]);
    }

    public function test_days_option_overrides_the_configured_retention_period(): void
    {
        $now = Carbon::parse('2026-06-18 12:00:00');
        Carbon::setTestNow($now);
        config()->set('downloads.activity_retention_days', 180);

        $expired = $this->downloadEventAt($now->copy()->subDays(31));

        $this->artisan('downloads:prune-activity --days=30')
            ->assertSuccessful();

        $this->assertDatabaseMissing('download_events', ['id' => $expired->id]);
    }

    public function test_it_rejects_an_invalid_retention_period(): void
    {
        $this->artisan('downloads:prune-activity --days=0')
            ->expectsOutput('The retention period must be a positive number of days.')
            ->assertFailed();
    }

    private function downloadEventAt(Carbon $createdAt): DownloadEvent
    {
        $event = new DownloadEvent;
        $event->forceFill([
            'download_kind' => 'archive',
            'file_type' => 'full',
            'filename' => 'example.zip',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        $event->save();

        return $event;
    }
}
