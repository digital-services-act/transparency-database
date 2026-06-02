<?php

namespace App\Console\Commands;

use App\Jobs\StatementElasticSearchableChunk;
use App\Jobs\StatementElasticSearchableChunkReverse;
use App\Jobs\StatementElasticSearchableRawChunk;
use App\Jobs\StatementElasticSearchableRawChunkReverse;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class ElasticIndexingStats extends Command
{
    private const INDEXING_JOB_CLASSES = [
        StatementElasticSearchableChunk::class => [
            'label' => 'Eloquent Forward',
            'source' => 'eloquent',
            'direction' => 'forward',
        ],
        StatementElasticSearchableChunkReverse::class => [
            'label' => 'Eloquent Reverse',
            'source' => 'eloquent',
            'direction' => 'reverse',
        ],
        StatementElasticSearchableRawChunk::class => [
            'label' => 'Raw Forward',
            'source' => 'raw',
            'direction' => 'forward',
        ],
        StatementElasticSearchableRawChunkReverse::class => [
            'label' => 'Raw Reverse',
            'source' => 'raw',
            'direction' => 'reverse',
        ],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:indexing-stats
        {--interval=5 : Polling interval in seconds}
        {--once : Display the current stats once and exit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor statement Elasticsearch indexing job progress';

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
        $interval = max(1, (int) $this->option('interval'));

        if ($this->option('once')) {
            $this->displayStats(false);

            return;
        }

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
    private function displayStats(bool $clearScreen = true): void
    {
        $timestamp = Carbon::now();
        $jobs = $this->getElasticIndexingJobs();

        if ($clearScreen) {
            $this->output->write("\033[2J\033[H");
        }

        $this->info('ElasticSearch Indexing Stats - '.$timestamp->format('Y-m-d H:i:s'));
        $this->line(str_repeat('=', 60));

        if (empty($jobs)) {
            $this->warn('No statement Elasticsearch indexing jobs found in queue');
            $this->displayHistoricalSummary();

            return;
        }

        $jobTypeSummaries = $this->summarizeJobsByType($jobs);
        $chainSummaries = $this->summarizeJobsByChain($jobs);
        $currentJob = $this->findMostAdvancedJob($jobs);

        $this->addToHistory($timestamp, $currentJob, count($jobs), $jobTypeSummaries, $chainSummaries);

        $this->displayCurrentStats($currentJob, count($jobs));

        $this->displayJobTypeSummary($jobTypeSummaries);

        $this->displayChainSummary($chainSummaries);

        $this->displayRateAndEta($jobTypeSummaries);

        $this->displayChainRateAndEta($chainSummaries);

        $this->displayActiveJobsSummary($jobs);

        $this->displayHistoricalSummary();
    }

    /**
     * Get all statement Elasticsearch indexing jobs from the queue
     */
    private function getElasticIndexingJobs(): array
    {
        $jobs = DB::table('jobs')
            ->whereLike('payload', '%StatementElasticSearchable%Chunk%')
            ->get();

        $parsedJobs = [];

        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);

            if (! in_array($payload['displayName'] ?? null, array_keys(self::INDEXING_JOB_CLASSES), true)) {
                continue;
            }

            $serializedCommand = $payload['data']['command'] ?? null;
            if (! is_string($serializedCommand)) {
                continue;
            }

            try {
                $command = unserialize($serializedCommand, [
                    'allowed_classes' => array_keys(self::INDEXING_JOB_CLASSES),
                ]);
            } catch (Throwable) {
                continue;
            }

            $jobClass = $this->matchedIndexingJobClass($command);
            if ($jobClass !== null) {
                $metadata = self::INDEXING_JOB_CLASSES[$jobClass];
                $chainBoundary = $metadata['direction'] === 'reverse' ? $command->min : $command->max;
                $chainKey = $jobClass.':'.$chainBoundary;

                $parsedJobs[] = [
                    'id' => $job->id,
                    'class' => $jobClass,
                    'chain_key' => $chainKey,
                    'chain_boundary' => $chainBoundary,
                    'label' => $metadata['label'],
                    'source' => $metadata['source'],
                    'direction' => $metadata['direction'],
                    'min' => $command->min,
                    'max' => $command->max,
                    'chunk' => $command->chunk,
                    'position' => $metadata['direction'] === 'reverse' ? $command->max : $command->min,
                    'remaining' => max(0, $command->max - $command->min),
                    'range' => isset($command->range) ? $command->range : true,
                    'benchmark' => isset($command->benchmark) ? $command->benchmark : false,
                    'created_at' => $job->created_at,
                    'attempts' => $job->attempts,
                    'reserved_at' => $job->reserved_at,
                ];
            }
        }

        return $parsedJobs;
    }

    /**
     * Find the job with the smallest remaining range.
     */
    private function findMostAdvancedJob(array $jobs): array
    {
        return collect($jobs)
            ->sortBy([
                ['remaining', 'asc'],
                ['id', 'desc'],
            ])
            ->first();
    }

    /**
     * Add current state to history for rate calculations
     */
    private function addToHistory(
        Carbon $timestamp,
        array $currentJob,
        int $jobCount,
        array $jobTypeSummaries,
        array $chainSummaries,
    ): void {
        $this->history[] = [
            'timestamp' => $timestamp,
            'min' => $currentJob['min'],
            'max' => $currentJob['max'],
            'remaining' => $currentJob['remaining'],
            'label' => $currentJob['label'],
            'job_count' => $jobCount,
            'job_types' => $jobTypeSummaries,
            'chains' => $chainSummaries,
            'total_chain_remaining' => array_sum(array_column($chainSummaries, 'remaining')),
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
        $remaining = $job['remaining'];

        $this->table(
            ['Metric', 'Value'],
            [
                ['Current Job Type', $job['label']],
                ['Chain Boundary', number_format($job['chain_boundary'])],
                ['Source', $job['source']],
                ['Direction', $job['direction']],
                ['Current Position', number_format($job['position'])],
                ['Current Position (min)', number_format($min)],
                ['Target Maximum', number_format($max)],
                ['Chunk Size', number_format($chunk)],
                ['Range Remaining', number_format($remaining)],
                ['Range Query Mode', $job['range'] ? 'range() ids' : 'whereBetween'],
                ['Benchmark Enabled', $job['benchmark'] ? 'yes' : 'no'],
                ['Active Jobs', $totalJobs],
            ]
        );
    }

    /**
     * Display summary of each indexing job type.
     */
    private function displayJobTypeSummary(array $jobTypeSummaries): void
    {
        $this->line("\nJob Type Summary:");

        $rows = [];
        foreach ($jobTypeSummaries as $summary) {
            $rows[] = [
                $summary['label'],
                $summary['active_jobs'],
                number_format($summary['best_remaining']),
                number_format($summary['remaining']),
                number_format($summary['queued_remaining']),
                $summary['benchmark_jobs'].'/'.$summary['active_jobs'],
            ];
        }

        $this->table(
            ['Job Type', 'Active', 'Best Remaining', 'Chain Remaining', 'Queued Remaining', 'Benchmark'],
            $rows
        );
    }

    /**
     * Display summary of each independent indexing chain.
     */
    private function displayChainSummary(array $chainSummaries): void
    {
        $this->line("\nChain Summary:");

        $rows = [];
        foreach ($chainSummaries as $summary) {
            $rows[] = [
                $summary['job_type'],
                number_format($summary['chain_boundary']),
                $summary['active_jobs'],
                number_format($summary['position']),
                number_format($summary['remaining']),
                number_format($summary['queued_remaining']),
                number_format($summary['chunk']),
                $summary['benchmark_jobs'].'/'.$summary['active_jobs'],
            ];
        }

        $this->table(
            ['Job Type', 'Boundary', 'Active', 'Position', 'Remaining', 'Queued Remaining', 'Chunk', 'Benchmark'],
            $rows
        );
    }

    /**
     * Display processing rates and ETA by indexing job type.
     */
    private function displayRateAndEta(array $jobTypeSummaries): void
    {
        if (count($this->history) < 2) {
            $this->line("\nProcessing Rates: Need more data points...");

            return;
        }

        $recent = end($this->history);
        $previous = $this->history[count($this->history) - 2];
        $timeDiff = abs($previous['timestamp']->diffInSeconds($recent['timestamp']));

        $this->line("\nProcessing Rates:");

        $rows = [];
        foreach ($jobTypeSummaries as $label => $summary) {
            if ($summary['active_jobs'] === 0) {
                $rows[] = [
                    $summary['label'],
                    'inactive',
                    '-',
                    number_format($summary['remaining']),
                ];

                continue;
            }

            $previousSummary = $previous['job_types'][$label] ?? null;

            if ($timeDiff <= 0 || $previousSummary === null || $previousSummary['active_jobs'] === 0) {
                $rows[] = [
                    $summary['label'],
                    'calculating',
                    'calculating',
                    number_format($summary['remaining']),
                ];

                continue;
            }

            $progressDiff = $previousSummary['remaining'] - $summary['remaining'];

            if ($progressDiff > 0) {
                $rate = $progressDiff / $timeDiff;
                $etaSeconds = $rate > 0 ? (int) round($summary['remaining'] / $rate) : 0;
                $eta = $etaSeconds > 0 ? Carbon::now()->addSeconds($etaSeconds) : null;

                $rows[] = [
                    $summary['label'],
                    number_format($rate, 2),
                    $eta ? $eta->format('Y-m-d H:i:s').' ('.$this->formatDuration($etaSeconds).')' : 'complete',
                    number_format($summary['remaining']),
                ];

                continue;
            }

            $rows[] = [
                $summary['label'],
                $progressDiff === 0 ? '0.00' : 'reset/new range',
                $progressDiff === 0 ? 'no movement' : 'calculating',
                number_format($summary['remaining']),
            ];
        }

        $this->table(['Job Type', 'Items/sec', 'ETA', 'Remaining'], $rows);
    }

    /**
     * Display processing rates and ETA by independent indexing chain.
     */
    private function displayChainRateAndEta(array $chainSummaries): void
    {
        if (count($this->history) < 2) {
            $this->line("\nChain Rates: Need more data points...");

            return;
        }

        $recent = end($this->history);
        $previous = $this->history[count($this->history) - 2];
        $timeDiff = abs($previous['timestamp']->diffInSeconds($recent['timestamp']));

        $this->line("\nChain Rates:");

        $rows = [];
        foreach ($chainSummaries as $chainKey => $summary) {
            $previousSummary = $previous['chains'][$chainKey] ?? null;

            if ($timeDiff <= 0 || $previousSummary === null) {
                $rows[] = [
                    number_format($summary['chain_boundary']),
                    $summary['job_type'],
                    'calculating',
                    'calculating',
                    number_format($summary['remaining']),
                ];

                continue;
            }

            $progressDiff = $previousSummary['remaining'] - $summary['remaining'];

            if ($progressDiff > 0) {
                $rate = $progressDiff / $timeDiff;
                $etaSeconds = $rate > 0 ? (int) round($summary['remaining'] / $rate) : 0;
                $eta = $etaSeconds > 0 ? Carbon::now()->addSeconds($etaSeconds) : null;

                $rows[] = [
                    number_format($summary['chain_boundary']),
                    $summary['job_type'],
                    number_format($rate, 2),
                    $eta ? $eta->format('Y-m-d H:i:s').' ('.$this->formatDuration($etaSeconds).')' : 'complete',
                    number_format($summary['remaining']),
                ];

                continue;
            }

            $rows[] = [
                number_format($summary['chain_boundary']),
                $summary['job_type'],
                $progressDiff === 0 ? '0.00' : 'reset/new range',
                $progressDiff === 0 ? 'no movement' : 'calculating',
                number_format($summary['remaining']),
            ];
        }

        $this->table(['Boundary', 'Job Type', 'Items/sec', 'ETA', 'Remaining'], $rows);
    }

    /**
     * Build per-type summaries for display and rate calculations.
     */
    private function summarizeJobsByType(array $jobs): array
    {
        $summaries = [];

        foreach (self::INDEXING_JOB_CLASSES as $metadata) {
            $summaries[$metadata['label']] = [
                'label' => $metadata['label'],
                'active_jobs' => 0,
                'best_remaining' => 0,
                'remaining' => 0,
                'queued_remaining' => 0,
                'benchmark_jobs' => 0,
            ];
        }

        foreach (collect($jobs)->groupBy('label')->sortKeys() as $label => $groupedJobs) {
            $mostAdvanced = $groupedJobs->sortBy('remaining')->first();
            $chainRemaining = $groupedJobs
                ->groupBy('chain_key')
                ->sum(fn ($chainJobs): int => $chainJobs->sortBy('remaining')->first()['remaining']);

            $summaries[$label] = [
                'label' => $label,
                'active_jobs' => $groupedJobs->count(),
                'best_remaining' => $mostAdvanced['remaining'],
                'remaining' => $chainRemaining,
                'queued_remaining' => $groupedJobs->sum('remaining'),
                'benchmark_jobs' => $groupedJobs->where('benchmark', true)->count(),
            ];
        }

        return $summaries;
    }

    /**
     * Build per-chain summaries for display and rate calculations.
     */
    private function summarizeJobsByChain(array $jobs): array
    {
        $summaries = [];

        foreach (collect($jobs)->groupBy('chain_key') as $chainKey => $groupedJobs) {
            $mostAdvanced = $groupedJobs->sortBy('remaining')->first();

            $summaries[$chainKey] = [
                'chain_key' => $chainKey,
                'chain_boundary' => $mostAdvanced['chain_boundary'],
                'job_type' => $mostAdvanced['label'],
                'source' => $mostAdvanced['source'],
                'direction' => $mostAdvanced['direction'],
                'active_jobs' => $groupedJobs->count(),
                'min' => $mostAdvanced['min'],
                'max' => $mostAdvanced['max'],
                'position' => $mostAdvanced['position'],
                'remaining' => $mostAdvanced['remaining'],
                'queued_remaining' => $groupedJobs->sum('remaining'),
                'chunk' => $mostAdvanced['chunk'],
                'benchmark_jobs' => $groupedJobs->where('benchmark', true)->count(),
            ];
        }

        uasort($summaries, static function (array $left, array $right): int {
            return [$left['source'], $left['direction'], $left['chain_boundary']]
                <=> [$right['source'], $right['direction'], $right['chain_boundary']];
        });

        return $summaries;
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
        $headers = ['Job ID', 'Job Type', 'Boundary', 'Min', 'Max', 'Position', 'Remaining', 'Chunk', 'Attempts', 'Benchmark'];
        $rows = [];

        foreach (collect($jobs)->sortBy([['chain_boundary', 'asc'], ['remaining', 'asc']])->take(16) as $job) {
            $rows[] = [
                $job['id'],
                $job['label'],
                number_format($job['chain_boundary']),
                number_format($job['min']),
                number_format($job['max']),
                number_format($job['position']),
                number_format($job['remaining']),
                number_format($job['chunk']),
                $job['attempts'],
                $job['benchmark'] ? 'yes' : 'no',
            ];
        }

        $this->table($headers, $rows);

        if (count($jobs) > 16) {
            $this->line('... and '.(count($jobs) - 16).' more jobs');
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

        $totalTime = abs($oldest['timestamp']->diffInSeconds($newest['timestamp']));
        $oldestRemaining = $oldest['total_chain_remaining'] ?? $oldest['remaining'];
        $newestRemaining = $newest['total_chain_remaining'] ?? $newest['remaining'];
        $totalProgress = max(0, $oldestRemaining - $newestRemaining);

        if ($totalTime > 0) {
            $avgRate = $totalProgress / $totalTime;
            $this->line('├─ Average rate: '.number_format($avgRate, 2).' items/second');
            $this->line('├─ Total progress: '.number_format($totalProgress).' items');
            $this->line('└─ Time span: '.$this->formatDuration($totalTime));
        }
    }

    private function matchedIndexingJobClass(mixed $command): ?string
    {
        foreach (array_keys(self::INDEXING_JOB_CLASSES) as $jobClass) {
            if ($command instanceof $jobClass) {
                return $jobClass;
            }
        }

        return null;
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
