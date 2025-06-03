<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
 */
class StatementsDayArchiveRangeZ extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive-range-z
                            {start_date : Start date (YYYY-MM-DD or relative like "2023-01-01")}
                            {end_date : End date (YYYY-MM-DD or relative like "yesterday")}
                            {--dry-run : Show what dates would be processed without executing}
                            {--delay=0 : Delay in seconds between each archive job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run day archive jobs for a date range';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $startDate = $this->parseDateArgument($this->argument('start_date'));
            $endDate = $this->parseDateArgument($this->argument('end_date'));
            $dryRun = $this->option('dry-run');
            $delay = (int) $this->option('delay');

            // Validate date range
            if ($startDate->gt($endDate)) {
                $this->error('Start date must be before or equal to end date');
                return 1;
            }

            // Calculate total days
            $totalDays = $startDate->diffInDays($endDate) + 1;

            $this->info("Archive range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
            $this->info("Total days to process: {$totalDays}");

            if ($dryRun) {
                $this->warn('DRY RUN MODE - No archives will be created');
                $this->listDates($startDate, $endDate);
                return 0;
            }

            // Confirm before proceeding
            if (!$this->confirm('Do you want to proceed with archiving these dates?')) {
                $this->info('Archive operation cancelled');
                return 0;
            }

            // Process each date
            $current = $startDate->copy();
            $processed = 0;
            $failed = 0;

            $this->output->progressStart($totalDays);

            while ($current->lte($endDate)) {
                $dateString = $current->format('Y-m-d');

                try {
                    $this->info("\nProcessing archive for: {$dateString}");

                    $exitCode = Artisan::call('statements:day-archive-z', [
                        'date' => $dateString
                    ]);

                    if ($exitCode === 0) {
                        $processed++;
                        $this->info("✓ Archive completed for {$dateString}");
                    } else {
                        $failed++;
                        $this->error("✗ Archive failed for {$dateString} (exit code: {$exitCode})");
                        Log::error("Archive failed for date: {$dateString}", [
                            'command' => 'statements:day-archive-range',
                            'exit_code' => $exitCode
                        ]);
                    }

                } catch (\Exception $e) {
                    $failed++;
                    $this->error("✗ Exception during archive for {$dateString}: " . $e->getMessage());
                    Log::error("Archive exception for date: {$dateString}", [
                        'command' => 'statements:day-archive-range',
                        'exception' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                $this->output->progressAdvance();

                // Add delay if specified
                if ($delay > 0 && $current->lt($endDate)) {
                    $this->info("Waiting {$delay} seconds before next archive...");
                    sleep($delay);
                }

                $current->addDay();
            }

            $this->output->progressFinish();

            // Summary
            $this->info("\n" . str_repeat('=', 50));
            $this->info("Archive Range Summary:");
            $this->info("Processed: {$processed} days");
            $this->info("Failed: {$failed} days");
            $this->info("Total: {$totalDays} days");

            if ($failed > 0) {
                $this->warn("Some archives failed. Check logs for details.");
                return 1;
            }

            $this->info("All archives completed successfully!");
            return 0;

        } catch (\Exception $e) {
            $this->error("Command failed: " . $e->getMessage());
            Log::error("StatementsDayArchiveRange command failed", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Parse date argument (handles both absolute and relative dates)
     */
    private function parseDateArgument(string $date): Carbon
    {
        // Handle relative dates like "yesterday", "today", etc.
        if (in_array($date, ['yesterday', 'today', 'tomorrow'])) {
            return Carbon::parse($date);
        }

        // Handle relative formats like "1 week ago", "2 days ago"
        if (preg_match('/^\d+\s+(day|week|month|year)s?\s+ago$/', $date)) {
            return Carbon::parse($date);
        }

        // Handle absolute dates
        try {
            return Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            // Try parsing as a general date string
            return Carbon::parse($date);
        }
    }

    /**
     * List all dates that would be processed
     */
    private function listDates(Carbon $startDate, Carbon $endDate): void
    {
        $this->info("\nDates that would be processed:");
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $this->line("  - {$current->format('Y-m-d')} ({$current->format('l')})");
            $current->addDay();
        }
    }
}
