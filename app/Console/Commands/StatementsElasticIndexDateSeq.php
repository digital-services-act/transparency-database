<?php

namespace App\Console\Commands;

use App\Jobs\StatementElasticSearchableChunk;
use App\Services\DayArchiveService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * @codeCoverageIgnore
 */
class StatementsElasticIndexDateSeq extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:elastic-index-date-seq
        {date=yesterday}
        {chunk=500}
        {range=true}
        {--benchmark : Log per-chunk indexing timing metrics from the queued indexing jobs.}
        {--skip-id-range=* : Inclusive statement ID range(s) to skip while queueing indexing jobs, format start:end. May be repeated.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elastic Index statements for a day';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        Log::info('Step 1');
        $chunk = $this->intifyArgument('chunk');
        Log::info('Step 2');
        $date = $this->sanitizeDateArgument();
        $use_range = $this->boolifyArgument('range');
        $benchmark = (bool) $this->option('benchmark');

        Log::info('Step 3');
        $min = $day_archive_service->getFirstIdOfDate($date);
        Log::info('Step 4');
        $max = $day_archive_service->getLastIdOfDate($date);

        if ($min && $max) {
            $skip_id_ranges = $this->skipIdRanges();
            if ($skip_id_ranges !== []) {
                $this->warn('Skipping statement ID range(s): '.$this->formatSkipIdRanges($skip_id_ranges));
                Log::warning('StatementsElasticIndexDateSeq: Skipping statement ID range(s) for '.$date->format('Y-m-d'), [
                    'skip_id_ranges' => $skip_id_ranges,
                ]);
            }

            Log::info('Step 5');
            Log::info('Indexing started for date: '.$date->format('Y-m-d').' at '.Carbon::now()->format('Y-m-d H:i:s'));

            $ranges = $this->statementIdRangesToIndex($min, $max, $skip_id_ranges);
            if ($ranges === []) {
                Log::warning('No indexing jobs queued for date: '.$date->format('Y-m-d'), [
                    'min' => $min,
                    'max' => $max,
                    'skip_id_ranges' => $skip_id_ranges,
                ]);

                return;
            }

            foreach ($ranges as $range) {
                StatementElasticSearchableChunk::dispatch($range['start'], $range['end'], $chunk, $use_range, $benchmark);
            }
        } else {
            Log::info('Step 6');
            Log::warning('Not able to obtain the highest or lowest ID for the day: '.$date->format('Y-m-d'));
        }
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

    private function statementIdRangesToIndex(int $min, int $max, array $skip_id_ranges): array
    {
        $ranges = [];
        $current = $min;

        foreach ($skip_id_ranges as $skip_id_range) {
            if ($skip_id_range['end'] < $current) {
                continue;
            }

            if ($skip_id_range['start'] > $max) {
                break;
            }

            if ($skip_id_range['start'] > $current) {
                $ranges[] = [
                    'start' => $current,
                    'end' => min($skip_id_range['start'] - 1, $max),
                ];
            }

            $current = max($current, $skip_id_range['end'] + 1);

            if ($current > $max) {
                break;
            }
        }

        if ($current <= $max) {
            $ranges[] = [
                'start' => $current,
                'end' => $max,
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
