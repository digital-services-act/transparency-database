<?php

namespace App\Console\Commands;

use App\Models\DayArchive;
use App\Services\TeamsWebhookService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use JsonException;
use RuntimeException;

class CheckDailyDataExports extends Command
{
    private const string AGGREGATES_URL = 'https://transparency.dsa.ec.europa.eu/explore-data/download/aggregates-%s.json';

    private const string DATE_TIMEZONE = 'Europe/Brussels';

    protected $signature = 'exports:check-yesterday';

    protected $description = 'Spot-check yesterday\'s production aggregates and global CSV archive.';

    public function handle(TeamsWebhookService $teamsWebhook): int
    {
        $date = Carbon::yesterday(self::DATE_TIMEZONE);
        $aggregateUrl = sprintf(self::AGGREGATES_URL, $date->format('Y-m-d'));
        [$aggregateStatus, $aggregatePassed] = $this->checkAggregates($aggregateUrl);
        $archive = $this->globalArchiveFor($date);
        [$csvStatus, $csvPassed] = $this->formatCsvStatus($archive);

        $report = [
            $date->format('Y/m/d'),
            $aggregateStatus,
            $csvStatus,
            sprintf('failed_jobs -> %s rows', number_format(DB::table('failed_jobs')->count(), 0, '.', ' ')),
            sprintf('statements_beta min(created_at) -> %s', DB::table('statements_beta')->min('created_at') ?? 'N/A'),
            sprintf('platform_puids min(created_at) -> %s', DB::table('platform_puids')->min('created_at') ?? 'N/A'),
        ];
        $message = implode("\n", $report);

        foreach ($report as $line) {
            $this->line($line);
        }

        if (config('app.env') === 'production') {
            try {
                $response = $teamsWebhook->sendTextCard($message);
            } catch (RuntimeException $exception) {
                $this->error($exception->getMessage());

                return self::FAILURE;
            } catch (ConnectionException) {
                $this->error('Could not connect to the daily Teams webhook.');

                return self::FAILURE;
            }

            if (! $response->successful()) {
                $this->error("The daily Teams webhook returned HTTP {$response->status()}.");

                return self::FAILURE;
            }

            $this->info('Teams card sent.');
        }

        return $aggregatePassed && $csvPassed ? self::SUCCESS : self::FAILURE;
    }

    /** @return array{string, bool} */
    private function checkAggregates(string $url): array
    {
        try {
            $response = Http::withOptions(['allow_redirects' => ['max' => 5]])
                ->connectTimeout(10)
                ->timeout(120)
                ->get($url);
        } catch (ConnectionException) {
            return ['Aggregates -> Connection failed', false];
        }

        if (! $response->successful()) {
            return [sprintf('Aggregates -> %d %s', $response->status(), $response->reason()), false];
        }

        try {
            $payload = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return ['Aggregates -> Invalid JSON', false];
        }

        if (! is_array($payload) || ! is_array($payload['aggregates'] ?? null)) {
            return ['Aggregates -> Unexpected JSON format', false];
        }

        return [sprintf('Aggregates -> Good %d Combos', count($payload['aggregates'])), true];
    }

    private function globalArchiveFor(Carbon $date): ?DayArchive
    {
        return DayArchive::query()
            ->whereDate('date', $date->toDateString())
            ->whereNull('platform_id')
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->first();
    }

    /** @return array{string, bool} */
    private function formatCsvStatus(?DayArchive $archive): array
    {
        if (! $archive) {
            return ['CSVs -> Not present!', false];
        }

        $total = number_format((int) $archive->total, 0, '.', ' ');
        $sizeInGigabytes = number_format((int) round((int) $archive->size / (1024 ** 3)), 0, '.', ' ');

        return [sprintf('CSVs -> Good, SORs total: %s full size: %s GB', $total, $sizeInGigabytes), true];
    }
}
