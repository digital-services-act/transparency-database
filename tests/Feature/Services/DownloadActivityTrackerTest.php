<?php

namespace Tests\Feature\Services;

use App\Models\DownloadEvent;
use App\Services\DownloadActivityTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DownloadActivityTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_an_event_without_a_session_hash_when_the_request_has_no_session(): void
    {
        $request = Request::create('/aggregates/2026-06-18.csv', 'GET');

        app(DownloadActivityTracker::class)->trackAggregate(
            $request,
            '2026-06-18',
            'csv',
            'aggregates-2026-06-18.csv'
        );

        $event = DownloadEvent::query()->sole();

        $this->assertNull($event->session_hash);
        $this->assertNull($event->route_name);
        $this->assertSame('aggregate', $event->download_kind);
    }

    public function test_it_logs_and_suppresses_persistence_failures(): void
    {
        Schema::drop('download_events');

        Log::shouldReceive('warning')
            ->once()
            ->with(
                'Failed to record download activity.',
                \Mockery::on(static fn (array $context): bool => isset($context['exception'], $context['message'])
                    && str_contains($context['message'], 'download_events'))
            );

        app(DownloadActivityTracker::class)->trackAggregate(
            Request::create('/aggregates/2026-06-18.csv', 'GET'),
            '2026-06-18',
            'csv',
            'aggregates-2026-06-18.csv'
        );

        $this->assertTrue(true);
    }
}
