<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @codeCoverageIgnore
 */
class ElasticIndexingStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:indexing-stats {--interval=5 : Polling interval in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor StatementElasticSearchableChunk indexing job progress';

    /**
     * Historical data for rate calculations (last 20 polls)
     */
    private array $history = [];

    /**
     * Maximum number of historical records to keep
     */
    private int $maxHistory = 20;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $interval = (int) $this->option('interval');

        $this->info("Monitoring ElasticSearch indexing jobs (polling every {$interval} seconds)");
        $this->info("Press Ctrl+C to stop monitoring\n");

        // Set up signal handler for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function () {
                $this->info("\nStopping monitor...");
                exit(0);
            });
        }

        while (true) {
            $this->displayStats();

            // Process signals if available
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            sleep($interval);
        }
    }

    /**
     * Display current indexing statistics
     */
    private function displayStats(): void
    {
        $timestamp = Carbon::now();
        $jobs = $this->getElasticIndexingJobs();

        // Clear screen and show header
        $this->output->write("\033[2J\033[H"); // Clear screen and move cursor to top
        $this->info('ElasticSearch Indexing Stats - '.$timestamp->format('Y-m-d H:i:s'));
        $this->line(str_repeat('=', 60));

        if (empty($jobs)) {
            $this->warn('No StatementElasticSearchableChunk jobs found in queue');
            $this->displayHistoricalSummary();

            return;
        }

        // Find the job with highest min (most progress)
        $currentJob = $this->findMostAdvancedJob($jobs);

        // Store current state in history
        $this->addToHistory($timestamp, $currentJob, count($jobs));

        // Display current stats
        $this->displayCurrentStats($currentJob, count($jobs));

        // Display rate and ETA if we have historical data
        $this->displayRateAndEta($currentJob);

        // Display all active jobs summary
        $this->displayActiveJobsSummary($jobs);

        // Display historical summary
        $this->displayHistoricalSummary();
    }

    /**
     * Get all StatementElasticSearchableChunk jobs from the queue
     */
    private function getElasticIndexingJobs(): array
    {
        $jobs = DB::table('jobs')
            ->whereRaw("JSON_EXTRACT(payload, '$.displayName') = ?", ['App\\Jobs\\StatementElasticSearchableChunk'])
            ->get();

        $parsedJobs = [];

        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            if (isset($payload['data']['command'])) {
                // Unserialize the command to get min, max, chunk
                $command = unserialize($payload['data']['command']);
                if ($command && isset($command->min, $command->max, $command->chunk)) {
                    $parsedJobs[] = [
                        'id' => $job->id,
                        'min' => $command->min,
                        'max' => $command->max,
                        'chunk' => $command->chunk,
                        'created_at' => $job->created_at,
                        'attempts' => $job->attempts,
                        'reserved_at' => $job->reserved_at,
                    ];
                }
            }
        }

        return $parsedJobs;
    }

    /**
     * Find the job with the highest min value (most progress)
     */
    private function findMostAdvancedJob(array $jobs): array
    {
        return collect($jobs)->sortByDesc('min')->first();
    }

    /**
     * Add current state to history for rate calculations
     */
    private function addToHistory(Carbon $timestamp, array $currentJob, int $jobCount): void
    {
        $this->history[] = [
            'timestamp' => $timestamp,
            'min' => $currentJob['min'],
            'max' => $currentJob['max'],
            'job_count' => $jobCount,
        ];

        // Keep only the last N records
        if (count($this->history) > $this->maxHistory) {
            array_shift($this->history);
        }
    }

    /**
     * Display current job statistics
     */
    private function displayCurrentStats(array $job, int $totalJobs): void
    {
        $min = $job['min'];
        $max = $job['max'];
        $chunk = $job['chunk'];

        $totalRange = $max - $min;
        $processed = 0; // Since min is the starting point for this job
        $remaining = $totalRange;
        $progressPercent = $totalRange > 0 ? 0 : 100; // This specific job hasn't started processing yet

        // Try to estimate overall progress if we have the original starting point
        // For now, we'll show progress from current min to max

        $this->table(
            ['Metric', 'Value'],
            [
                ['Current Position (min)', number_format($min)],
                ['Target Maximum', number_format($max)],
                ['Chunk Size', number_format($chunk)],
                ['Range Remaining', number_format($remaining)],
                ['Active Jobs', $totalJobs],
                ['Progress %', sprintf('%.2f%%', $progressPercent)],
            ]
        );
    }

    /**
     * Display processing rate and ETA
     */
    private function displayRateAndEta(array $currentJob): void
    {
        if (count($this->history) < 2) {
            $this->line("\nRate calculation: Need more data points...");

            return;
        }

        $recent = end($this->history);
        $previous = $this->history[count($this->history) - 2];

        $timeDiff = $recent['timestamp']->diffInSeconds($previous['timestamp']);
        $progressDiff = $previous['min'] - $recent['min']; // min decreases as we progress

        if ($timeDiff > 0 && $progressDiff !== 0) {
            $rate = abs($progressDiff) / $timeDiff;
            $remaining = $currentJob['max'] - $currentJob['min'];
            $etaSeconds = $rate > 0 ? $remaining / $rate : 0;

            $this->line("\nProcessing Rate:");
            $this->line('├─ Items/second: '.number_format($rate, 2));
            if ($etaSeconds > 0) {
                $eta = Carbon::now()->addSeconds($etaSeconds);
                $this->line('├─ ETA: '.$eta->format('Y-m-d H:i:s').' ('.$this->formatDuration($etaSeconds).')');
            }
        } else {
            $this->line("\nProcessing Rate: Calculating...");
        }
    }

    /**
     * Display summary of all active jobs
     */
    private function displayActiveJobsSummary(array $jobs): void
    {
        if (count($jobs) <= 1) {
            return;
        }

        $this->line("\nActive Jobs Summary:");
        $headers = ['Job ID', 'Min', 'Max', 'Chunk', 'Range', 'Attempts'];
        $rows = [];

        foreach (collect($jobs)->sortByDesc('min')->take(5) as $job) {
            $range = $job['max'] - $job['min'];
            $rows[] = [
                $job['id'],
                number_format($job['min']),
                number_format($job['max']),
                number_format($job['chunk']),
                number_format($range),
                $job['attempts'],
            ];
        }

        $this->table($headers, $rows);

        if (count($jobs) > 5) {
            $this->line('... and '.(count($jobs) - 5).' more jobs');
        }
    }

    /**
     * Display historical summary
     */
    private function displayHistoricalSummary(): void
    {
        if (count($this->history) < 2) {
            return;
        }

        $this->line("\nRecent History (last ".count($this->history).' polls):');

        $oldest = reset($this->history);
        $newest = end($this->history);

        $totalTime = $oldest['timestamp']->diffInSeconds($newest['timestamp']);
        $totalProgress = abs($oldest['min'] - $newest['min']);

        if ($totalTime > 0) {
            $avgRate = $totalProgress / $totalTime;
            $this->line('├─ Average rate: '.number_format($avgRate, 2).' items/second');
            $this->line('├─ Total progress: '.number_format($totalProgress).' items');
            $this->line('└─ Time span: '.$this->formatDuration($totalTime));
        }
    }

    /**
     * Format duration in human-readable format
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return "{$minutes}m {$remainingSeconds}s";
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m {$remainingSeconds}s";
    }
}
