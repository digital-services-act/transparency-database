<?php

namespace App\Console\Commands;

use App\Jobs\PlatformPuidDeleteChunk;
use App\Jobs\StatementDeleteChunk;
use App\Services\DayArchiveService;
use App\Services\StatementElasticSearchService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class StatementsRemoveDate extends Command
{
    use CommandTrait;

    private const int MAX_CHAINS = 4;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:remove-date
        {date=181}
        {chunk=10000}
        {chains=4 : Number of independent root delete chains to dispatch, capped at 4.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove (delete) statements for a day';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service, StatementElasticSearchService $statement_elastic_search_service): int
    {
        try {
            $chunk = $this->positiveIntArgument('chunk');
            $date = $this->sanitizeDateArgument();
            $chains = min(self::MAX_CHAINS, $this->positiveIntArgument('chains'));
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return Command::FAILURE;
        }

        $min = $day_archive_service->getFirstIdOfDate($date);
        $max = $day_archive_service->getLastIdOfDate($date);

        // Delete from Elasticsearch in one fast range task. The database work remains date-scoped below.
        $statement_elastic_search_service->deleteStatementsBeforeDate($date->copy()->addDay());

        if ($min && $max) {
            Log::info('Statement Removing Started', ['date' => $date->format('Y-m-d'), 'at' => Carbon::now()->format('Y-m-d H:i:s')]);
            $this->dispatchDeleteChains(StatementDeleteChunk::class, (int) $min, (int) $max, $chunk, $chains, $date);
        }

        // Now remove PlatformPuids too
        $ppmin = $day_archive_service->getFirstPlatformPuidIdOfDate($date);
        $ppmax = $day_archive_service->getLastPlatformPuidIdOfDate($date);
        if ($ppmin && $ppmax) {
            Log::info('PlatformPuid Removing Started', ['date' => $date->format('Y-m-d'), 'at' => Carbon::now()->format('Y-m-d H:i:s')]);
            $this->dispatchDeleteChains(PlatformPuidDeleteChunk::class, (int) $ppmin, (int) $ppmax, $chunk, $chains, $date);
        }

        if (! $min || ! $max) {
            Log::warning('Not able to obtain the highest or lowest ID for the day: '.$date->format('Y-m-d'));
        }

        if (! $ppmin || ! $ppmax) {
            Log::warning('Not able to obtain the highest or lowest PlatformPuid ID for the day: '.$date->format('Y-m-d'));
        }

        return Command::SUCCESS;
    }

    private function positiveIntArgument(string $name): int
    {
        $value = $this->intifyArgument($name);

        if ($value < 1) {
            throw new InvalidArgumentException(sprintf('The %s argument must be greater than zero.', $name));
        }

        return $value;
    }

    /**
     * @param  class-string<StatementDeleteChunk|PlatformPuidDeleteChunk>  $job
     */
    private function dispatchDeleteChains(string $job, int $min, int $max, int $chunk, int $chains, Carbon $date): void
    {
        $root_ranges = $this->splitRangeIntoChains($min, $max, $chains);

        Log::info('Queueing delete chains for date: '.$date->format('Y-m-d'), [
            'job' => $job,
            'min' => $min,
            'max' => $max,
            'chunk' => $chunk,
            'chains' => count($root_ranges),
        ]);

        foreach ($root_ranges as $range) {
            $job::dispatch($range['start'], $range['end'], $chunk, $date->format('Y-m-d'));
        }
    }

    private function splitRangeIntoChains(int $start, int $end, int $chains): array
    {
        $total = $end - $start + 1;
        $chain_count = min($chains, $total);
        $chain_size = (int) ceil($total / $chain_count);
        $ranges = [];

        for ($i = 0; $i < $chain_count; $i++) {
            $chain_start = $start + ($i * $chain_size);
            $chain_end = min($end, $chain_start + $chain_size - 1);

            if ($chain_start > $end) {
                break;
            }

            $ranges[] = [
                'start' => $chain_start,
                'end' => $chain_end,
            ];
        }

        return $ranges;
    }
}
