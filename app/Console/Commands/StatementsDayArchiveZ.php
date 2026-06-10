<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportArchiveZ;
use App\Jobs\StatementCsvExportCopyS3;
use App\Jobs\StatementCsvExportGroupParts;
use App\Jobs\StatementCsvExportSha1;
use App\Jobs\StatementCsvExportZ;
use App\Services\DayArchiveService;
use App\Services\DayArchiveWorkspace;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class StatementsDayArchiveZ extends Command
{
    use CommandTrait;

    private const CSV_EXPORT_JOB_CHUNK_SIZE = 1000000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:day-archive-z
        {date=yesterday}
        {--skip-id-range=* : Inclusive statement ID range(s) to skip while queueing CSV export jobs, format start:end. May be repeated.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a day archive compile jobs.';

    /**
     * Execute the console command.
     *
     * @throws Exception
     * @throws Throwable
     */
    public function handle(DayArchiveService $day_archive_service, DayArchiveWorkspace $day_archive_workspace): void
    {
        // if ( ! config('filesystems.disks.s3ds.bucket')) {
        //     Log::error('In order to make day archives, you need to define the "s3ds" bucket.');

        //     return;
        // }

        $date = $this->sanitizeDateArgument();
        $date_string = $date->format('Y-m-d');

        $exports = $day_archive_service->buildBasicExportsArray();
        $versions = ['full', 'light'];

        $first_id = $day_archive_service->getFirstIdOfDate($date);
        $last_id = $day_archive_service->getLastIdOfDate($date);

        if (! $first_id) {
            $this->error('No first_id found for date: '.$date_string.'. Aborting...');
            Log::error('StatementsDayArchiveZ: No first_id found for date: '.$date_string.'. Aborting...');

            return;
        }

        if (! $last_id) {
            $this->error('No last_id found for date: '.$date_string.'. Aborting...');
            Log::error('StatementsDayArchiveZ: No last_id found for date: '.$date_string.'. Aborting...');

            return;
        }

        $skip_id_ranges = $this->skipIdRanges();
        if ($skip_id_ranges !== []) {
            $this->warn('Skipping statement ID range(s): '.$this->formatSkipIdRanges($skip_id_ranges));
            Log::warning('StatementsDayArchiveZ: Skipping statement ID range(s) for '.$date_string, [
                'skip_id_ranges' => $skip_id_ranges,
            ]);
        }

        $csv_export_jobs = $this->buildCsvExportJobs($date_string, $first_id, $last_id, $skip_id_ranges);
        if ($csv_export_jobs === []) {
            $this->error('No CSV export jobs generated for date: '.$date_string.'. Aborting...');
            Log::error('StatementsDayArchiveZ: No CSV export jobs generated for date: '.$date_string.'. Aborting...', [
                'first_id' => $first_id,
                'last_id' => $last_id,
                'skip_id_ranges' => $skip_id_ranges,
            ]);

            return;
        }

        // This will store with no compression the zips into one zip.
        $group_zip_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $group_zip_jobs[] = new StatementCsvExportGroupParts($date_string, $export['slug'], $version);
            }
        }

        // Generate sha1s for the main zip.
        $sha1_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $sha1_jobs[] = new StatementCsvExportSha1($date_string, $export['slug'], $version);
            }
        }

        // Copy what we need to s3
        $copys3_jobs = [];
        foreach ($exports as $export) {
            foreach ($versions as $version) {
                $zip = 'sor-'.$export['slug'].'-'.$date_string.'-'.$version.'.zip';
                $sha1 = 'sor-'.$export['slug'].'-'.$date_string.'-'.$version.'.zip.sha1';
                $copys3_jobs[] = new StatementCsvExportCopyS3($zip, $sha1);
            }
        }

        // Create DB Entries to show on the data download page.
        $archive_jobs = [];
        foreach ($exports as $export) {
            $archive_jobs[] = new StatementCsvExportArchiveZ($date_string, $export['slug'], $export['id']);
        }

        // Hold and carry all the possible jobs.
        $luggage = [
            'date_string' => $date_string,
            'archive_jobs' => $archive_jobs,
            'group_zip_jobs' => $group_zip_jobs,
            'csv_export_jobs' => $csv_export_jobs,
            'sha1_jobs' => $sha1_jobs,
            'copys3_jobs' => $copys3_jobs,
        ];

        Log::info('Day Archiving Started for: '.$luggage['date_string'].' at '.Carbon::now()->format('Y-m-d H:i:s'));
        $day_archive_workspace->deleteFilesForDate($date_string);
        $deleted_day_archives = $day_archive_service->deleteDayArchivesByDate($date);

        if ($deleted_day_archives > 0) {
            Log::info('Deleted '.$deleted_day_archives.' existing DayArchive rows for: '.$date_string);
        }

        Bus::batch($luggage['csv_export_jobs'])->onQueue('csv')->finally(static function () use ($luggage) {
            Bus::batch($luggage['group_zip_jobs'])->onQueue('zip')->finally(static function () use ($luggage) {
                Bus::batch($luggage['sha1_jobs'])->onQueue('sha1')->finally(static function () use ($luggage) {
                    Bus::batch($luggage['copys3_jobs'])->onQueue('s3copy')->finally(static function () use ($luggage) {
                        Bus::batch($luggage['archive_jobs'])->onQueue('archive')->finally(static function () use ($luggage) {
                            app(DayArchiveWorkspace::class)->deleteFilesForDate($luggage['date_string']);
                            Log::info('Day Archiving Ended for: '.$luggage['date_string'].' at '.Carbon::now()->format('Y-m-d H:i:s'));
                        })->dispatch();
                    })->dispatch();
                })->dispatch();
            })->dispatch();
        })->dispatch();
    }

    private function skipIdRanges(): array
    {
        $ranges = [];

        foreach ((array) $this->option('skip-id-range') as $range) {
            if (! preg_match('/^\s*(\d+)\s*(?::|,|-)\s*(\d+)\s*$/', (string) $range, $matches)) {
                throw new InvalidArgumentException('Invalid --skip-id-range value. Expected format: start:end');
            }

            $start = (int) $matches[1];
            $end = (int) $matches[2];

            if ($start > $end) {
                throw new InvalidArgumentException('Invalid --skip-id-range value. Start ID must be less than or equal to end ID.');
            }

            $ranges[] = [
                'start' => $start,
                'end' => $end,
            ];
        }

        return $this->mergeSkipIdRanges($ranges);
    }

    private function mergeSkipIdRanges(array $ranges): array
    {
        if ($ranges === []) {
            return [];
        }

        usort($ranges, static fn (array $left, array $right): int => $left['start'] <=> $right['start']);

        $merged = [];
        foreach ($ranges as $range) {
            $last_index = count($merged) - 1;

            if ($last_index < 0 || $range['start'] > $merged[$last_index]['end'] + 1) {
                $merged[] = $range;

                continue;
            }

            $merged[$last_index]['end'] = max($merged[$last_index]['end'], $range['end']);
        }

        return $merged;
    }

    private function buildCsvExportJobs(string $date_string, int $first_id, int $last_id, array $skip_id_ranges): array
    {
        $csv_export_jobs = [];
        $part = 0;

        foreach ($this->statementIdRangesToExport($first_id, $last_id, $skip_id_ranges) as $range) {
            $current = $range['start'];

            while ($current <= $range['end']) {
                $till = min($current + self::CSV_EXPORT_JOB_CHUNK_SIZE, $range['end']);
                // $csv_export_jobs[] = new StatementCsvExport($date_string, sprintf('%05d', $part), $current, $till, $part === 0);
                // Always headers
                $csv_export_jobs[] = new StatementCsvExportZ($date_string, sprintf('%05d', $part), $current, $till, true);
                $part++;
                $current = $till + 1;
            }
        }

        return $csv_export_jobs;
    }

    private function statementIdRangesToExport(int $first_id, int $last_id, array $skip_id_ranges): array
    {
        $ranges = [];
        $current = $first_id;

        foreach ($skip_id_ranges as $skip_id_range) {
            if ($skip_id_range['end'] < $current) {
                continue;
            }

            if ($skip_id_range['start'] > $last_id) {
                break;
            }

            if ($skip_id_range['start'] > $current) {
                $ranges[] = [
                    'start' => $current,
                    'end' => min($skip_id_range['start'] - 1, $last_id),
                ];
            }

            $current = max($current, $skip_id_range['end'] + 1);

            if ($current > $last_id) {
                break;
            }
        }

        if ($current <= $last_id) {
            $ranges[] = [
                'start' => $current,
                'end' => $last_id,
            ];
        }

        return $ranges;
    }

    private function formatSkipIdRanges(array $skip_id_ranges): string
    {
        return implode(', ', array_map(
            static fn (array $range): string => $range['start'].'-'.$range['end'],
            $skip_id_ranges
        ));
    }
}
