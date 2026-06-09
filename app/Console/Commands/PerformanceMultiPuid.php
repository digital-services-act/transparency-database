<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Models\PlatformPuid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 */
class PerformanceMultiPuid extends Command
{
    protected $signature = 'performance:multi-puid
                            {--count=100 : Number of PUIDs to test}
                            {--platform-id=1 : Platform ID to use}';

    protected $description = 'Test PUID cache and database performance';

    private array $insertedIds = [];

    private array $cacheKeys = [];

    private int $lock_valid_seconds = 30;

    private int $cache_valid_days = 2;

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $platformId = (int) $this->option('platform-id');
        $platform = Platform::find($platformId);

        if (! $platform) {
            $this->error("❌ Platform with ID {$platformId} does not exist.");

            return 1;
        }

        $this->info('🧪 Starting PUID Performance Test');
        $this->info("PUIDs to test: {$count}");
        $this->info("Platform: {$platformId}");
        $this->newLine();

        // Generate fake UUIDs
        $this->info("📝 Generating {$count} fake UUIDs...");
        $puids = $this->generatePuids($count);

        try {
            $start = microtime(true);

            $failedLocks = [];
            $locks = [];

            foreach ($puids as $puid) {
                $key = $this->getCacheKey($platformId, $puid);
                $lockKey = "lock:puid:{{$key}}";

                $lock = Cache::lock($lockKey, $this->lock_valid_seconds);

                if (! $lock->get()) {
                    // @codeCoverageIgnoreStart
                    $failedLocks[] = $puid;
                    // @codeCoverageIgnoreEnd
                } else {
                    $locks[$puid] = $lock;
                }
            }

            if (! empty($failedLocks)) {
                // This should not happen, but in case it does, we log it and throw an exception
                $this->error('❌ Failed to acquire locks for PUIDs: '.implode(', ', $failedLocks).' on platform '.$platformId);

                return 1;
            }

            $duration = (microtime(true) - $start) * 1000;
            $this->info('🕒 LOCKING takes: '.number_format($duration, 2).'ms');
            $this->newLine();

            // Check cache then check puids table
            $this->checkDuplicatesInCache($puids, $platformId);
            $this->checkDuplicatesInPlatformPuids($puids, $platformId);

            // Add Puids to cache
            $this->addPuidsToCache($puids, $platformId);

            // Performance Summary
            $this->displaySummary();

            return 0;
        } finally {
            foreach ($locks as $lock) {
                optional($lock)->release();
            }
            $this->cleanup();

            $duration = (microtime(true) - $start) * 1000;
            $this->info('🕒 Total Test Duration: '.number_format($duration, 2).'ms');
        }
    }

    private function generatePuids(int $count): array
    {
        $puids = [];
        for ($i = 0; $i < $count; $i++) {
            $puids[] = (string) Str::uuid();
        }

        return $puids;
    }

    public function getCacheKey(int $platform_id, mixed $puid): string
    {
        return 'puid-'.$platform_id.'-'.$puid;
    }

    private function checkDuplicatesInCache(array $puids, int $platformId): void
    {
        $this->info('🔍 Test 1: Checking if PUIDs exist in cache (expect: not found)');

        $start = microtime(true);
        $duplicates = collect();

        foreach ($puids as $puid) {
            $key = $this->getCacheKey($platformId, $puid);
            if (Cache::has($key)) {
                $duplicates->add($puid);
            }
        }

        $duration = (microtime(true) - $start) * 1000;
        $perItem = $duration / count($puids);

        $this->line('  ✓ Checked '.count($puids).' PUIDs in '.number_format($duration, 2).'ms');
        $this->line('  ✓ Average: '.number_format($perItem, 3).'ms per PUID');
        $this->line("  ✓ Found: {$duplicates->count()} (expected 0)");
        $this->newLine();
    }

    private function addPuidsToCache(array $puids, int $platformId): void
    {
        $this->info('💾 Test 3: Adding PUIDs to cache');

        $start = microtime(true);
        $added = 0;

        foreach ($puids as $puid) {
            $key = $this->getCacheKey($platformId, $puid);
            $this->cacheKeys[] = $key;

            if (Cache::add($key, true, now()->addDays($this->cache_valid_days))) {
                $added++;
            }
        }

        $duration = (microtime(true) - $start) * 1000;
        $perItem = $duration / count($puids);

        $this->line('  ✓ Added '.$added.' PUIDs in '.number_format($duration, 2).'ms');
        $this->line('  ✓ Average: '.number_format($perItem, 3).'ms per PUID');
        $this->newLine();
    }

    private function checkDuplicatesInPlatformPuids(array $puids, int $platformId): void
    {
        $this->info('🔍 Test 2: Checking if PUIDs exist in database (expect: not found)');

        $start = microtime(true);

        $found = PlatformPuid::query()
            ->where('platform_id', $platformId)
            ->whereIn('puid', $puids)
            ->pluck('puid')
            ->toArray();

        $duration = (microtime(true) - $start) * 1000;

        $this->line("  ✓ DB PlatformPuid whereIn query took {$duration}ms");
        if (count($found)) {
            $this->line('  ✗ Found: '.count($found).': '.implode(', ', $found));
        }
        $this->newLine();
    }

    private function displaySummary(): void
    {
        $this->info('📊 Performance Summary');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Redis Connection', config('database.redis.default.host').':'.config('database.redis.default.port')],
                ['Database Connection', config('database.default')],
                ['Cache Driver', config('cache.default')],
                ['Total PUIDs Tested', count($this->cacheKeys)],
                ['Memory Usage', number_format(memory_get_peak_usage(true) / 1024 / 1024, 2).' MB'],
            ]
        );
        $this->newLine();
    }

    private function cleanup(): void
    {
        $this->info('🧹 Cleaning up test data...');

        // Remove from cache
        if (! empty($this->cacheKeys)) {
            $start = microtime(true);
            foreach ($this->cacheKeys as $key) {
                Cache::forget($key);
            }
            $duration = (microtime(true) - $start) * 1000;
            $this->line('  ✓ Removed '.count($this->cacheKeys).' cache entries in '.number_format($duration, 2).'ms');
        }

        $this->newLine();
        $this->info('✅ Cleanup complete!');
    }
}
