<?php

namespace App\Console\Commands;

use App\Models\DownloadEvent;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DownloadActivityStats extends Command
{
    protected $signature = 'downloads:stats
        {days=30 : Number of calendar days to include, including today}';

    protected $description = 'Display basic download activity statistics.';

    public function handle(): int
    {
        $days = $this->argument('days');

        if (filter_var($days, FILTER_VALIDATE_INT) === false || (int) $days < 1) {
            $this->error('The reporting period must be a positive number of days.');

            return self::FAILURE;
        }

        $days = (int) $days;
        $start = now()->startOfDay()->subDays($days - 1);
        $end = now();
        $query = $this->queryForPeriod($start, $end);

        $this->info("Download activity: {$start->toDateString()} through {$end->toDateString()} ({$days} days)");
        $this->line('Counts represent redirects to download URLs, not confirmed completed transfers.');

        $this->displaySummary($query);
        $this->displayBreakdown($query, 'download_kind', 'Request kinds', 'Kind');
        $this->displayBreakdown($query, 'file_type', 'File types', 'Type');
        $this->displayBreakdown($query, 'route_name', 'Routes', 'Route');
        $this->displayDailyActivity($query);

        return self::SUCCESS;
    }

    private function queryForPeriod(Carbon $start, Carbon $end): Builder
    {
        return DownloadEvent::query()
            ->whereBetween('created_at', [$start, $end]);
    }

    private function displaySummary(Builder $query): void
    {
        $total = (clone $query)->count();
        $uniqueSessions = (clone $query)
            ->whereNotNull('session_hash')
            ->distinct()
            ->count('session_hash');
        $repeatSessions = (clone $query)
            ->whereNotNull('session_hash')
            ->select('session_hash')
            ->groupBy('session_hash')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();
        $withoutSession = (clone $query)
            ->whereNull('session_hash')
            ->count();

        $this->newLine();
        $this->info('Summary');
        $this->table(['Metric', 'Value'], [
            ['Tracked requests', $total],
            ['Unique sessions', $uniqueSessions],
            ['Sessions with multiple requests', $repeatSessions],
            ['Requests without a session', $withoutSession],
        ]);
    }

    private function displayBreakdown(Builder $query, string $column, string $title, string $label): void
    {
        $rows = (clone $query)
            ->selectRaw("{$column}, COUNT(*) as requests")
            ->groupBy($column)
            ->orderByDesc('requests')
            ->orderBy($column)
            ->get()
            ->map(static fn (DownloadEvent $event): array => [
                $event->getAttribute($column) ?? '(unknown)',
                (int) $event->getAttribute('requests'),
            ])
            ->all();

        $this->newLine();
        $this->info($title);
        $this->table([$label, 'Requests'], $rows);
    }

    private function displayDailyActivity(Builder $query): void
    {
        $rows = (clone $query)
            ->selectRaw('DATE(created_at) as activity_date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(DISTINCT session_hash) as unique_sessions')
            ->selectRaw("SUM(CASE WHEN download_kind = 'archive' THEN 1 ELSE 0 END) as archives")
            ->selectRaw("SUM(CASE WHEN download_kind = 'checksum' THEN 1 ELSE 0 END) as checksums")
            ->selectRaw("SUM(CASE WHEN download_kind = 'aggregate' THEN 1 ELSE 0 END) as aggregates")
            ->groupByRaw('DATE(created_at)')
            ->orderBy('activity_date')
            ->get()
            ->map(static fn (DownloadEvent $event): array => [
                $event->getAttribute('activity_date'),
                (int) $event->getAttribute('total'),
                (int) $event->getAttribute('unique_sessions'),
                (int) $event->getAttribute('archives'),
                (int) $event->getAttribute('checksums'),
                (int) $event->getAttribute('aggregates'),
            ])
            ->all();

        $this->newLine();
        $this->info('Daily activity');
        $this->table(
            ['Date', 'Requests', 'Sessions', 'Archives', 'Checksums', 'Aggregates'],
            $rows
        );
    }
}
