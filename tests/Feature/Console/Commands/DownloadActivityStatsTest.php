<?php

namespace Tests\Feature\Console\Commands;

use App\Models\DownloadEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DownloadActivityStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_reports_basic_stats_for_the_default_30_days(): void
    {
        $now = Carbon::parse('2026-06-18 12:00:00');
        Carbon::setTestNow($now);

        $this->downloadEventAt($now->copy(), 'archive', 'full', 'session-a');
        $this->downloadEventAt($now->copy()->subDay(), 'aggregate', 'csv', 'session-a');
        $this->downloadEventAt($now->copy()->subDays(29), 'checksum', 'sha1', 'session-b');
        $this->downloadEventAt($now->copy()->subDays(30), 'archive', 'light', 'excluded-session');

        $this->assertSame(0, Artisan::call('downloads:stats'));

        $output = Artisan::output();

        $this->assertStringContainsString('2026-05-20 through 2026-06-18 (30 days)', $output);
        $this->assertStringContainsString('Tracked requests', $output);
        $this->assertStringContainsString('Unique sessions', $output);
        $this->assertStringContainsString('Sessions with multiple requests', $output);
        $this->assertStringContainsString('Request kinds', $output);
        $this->assertStringContainsString('archive', $output);
        $this->assertStringContainsString('aggregate', $output);
        $this->assertStringContainsString('checksum', $output);
        $this->assertStringContainsString('File types', $output);
        $this->assertStringContainsString('Routes', $output);
        $this->assertStringContainsString('dayarchive.download', $output);
        $this->assertStringContainsString('Daily activity', $output);
        $this->assertStringNotContainsString('excluded-session', $output);
    }

    public function test_custom_period_includes_events_from_that_number_of_calendar_days(): void
    {
        $now = Carbon::parse('2026-06-18 12:00:00');
        Carbon::setTestNow($now);

        $this->downloadEventAt($now->copy()->subDays(39), 'archive', 'light', 'session-a');
        $this->downloadEventAt($now->copy()->subDays(40), 'archive', 'full', 'excluded-session');

        $this->assertSame(0, Artisan::call('downloads:stats', ['days' => 40]));

        $output = Artisan::output();

        $this->assertStringContainsString('2026-05-10 through 2026-06-18 (40 days)', $output);
        $this->assertStringContainsString('light', $output);
        $this->assertStringNotContainsString('full', $output);
    }

    public function test_it_rejects_an_invalid_reporting_period(): void
    {
        $this->artisan('downloads:stats 0')
            ->expectsOutput('The reporting period must be a positive number of days.')
            ->assertFailed();
    }

    public function test_it_runs_successfully_without_download_activity(): void
    {
        $this->artisan('downloads:stats')
            ->expectsOutputToContain('Tracked requests')
            ->assertSuccessful();
    }

    private function downloadEventAt(
        Carbon $createdAt,
        string $kind,
        string $type,
        ?string $sessionHash
    ): DownloadEvent {
        $event = new DownloadEvent;
        $event->forceFill([
            'download_kind' => $kind,
            'file_type' => $type,
            'filename' => "{$kind}-{$type}.zip",
            'route_name' => 'dayarchive.download',
            'session_hash' => $sessionHash,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
        $event->save();

        return $event;
    }
}
