<?php

namespace App\Services;

use App\Exceptions\PuidNotUniqueMultipleException;
use App\Exceptions\PuidNotUniqueSingleException;
use App\Models\PlatformPuid;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PlatformUniqueIdService
{
    public $lock_valid_seconds = 30;

    public function __construct(protected int $cache_valid_days = 2) {}

    public function handlePuid(mixed $puid, int $platform_id): void
    {
        $key = $this->getCacheKey($platform_id, $puid);
        $lockKey = "lock:puid:{{$key}}";

        $lock = Cache::lock($lockKey, $this->lock_valid_seconds);

        // @codeCoverageIgnoreStart
        if (! $lock->get()) {
            Log::info('Lock encountered for PUID '.$puid.' on platform '.$platform_id);
            throw new PuidNotUniqueSingleException($puid);
        }
        // @codeCoverageIgnoreEnd

        try {
            $this->addPuidToCache($platform_id, $puid);
            $this->addPuidToDatabase($platform_id, $puid);
        } finally {
            optional($lock)->release();
        }
    }

    public function getCacheKey(int $platform_id, mixed $puid): string
    {
        return 'puid-'.$platform_id.'-'.$puid;
    }

    /**
     * @throws PuidNotUniqueSingleException
     */
    public function addPuidToCache($platform_id, $puid): void
    {
        if (! Cache::add($this->getCacheKey($platform_id, $puid), true, now()->addDays($this->cache_valid_days))) {
            throw new PuidNotUniqueSingleException($puid);
        }
    }

    /**
     * @throws PuidNotUniqueSingleException
     */
    public function addPuidToDatabase($platform_id, $puid): void
    {
        if (PlatformPuid::where('platform_id', $platform_id)->where('puid', $puid)->exists()) {
            throw new PuidNotUniqueSingleException($puid);
        }

        PlatformPuid::create([
            'puid' => $puid,
            'platform_id' => $platform_id,
        ]);
    }

    /**
     * Check if the platform identifiers are all unique.
     *
     *
     * @throws PuidNotUniqueMultipleException
     */
    public function checkDuplicatesInRequest(array $puids): bool
    {
        $uniquePuids = array_unique($puids);

        if (count($uniquePuids) !== count($puids)) {
            $counts = array_count_values($puids);
            $duplicates = array_filter($counts, static fn ($count) => $count > 1);
            $duplicates = array_keys($duplicates);

            throw new PuidNotUniqueMultipleException($duplicates);
        }

        return true;
    }

    public function isPuidInCache(int $platform_id, mixed $puid): bool
    {
        return Cache::get($this->getCacheKey($platform_id, $puid), false);
    }

    public function isPuidInDb(int $platform_id, mixed $puid): bool
    {
        return PlatformPuid::where('platform_id', $platform_id)->where('puid', $puid)->exists();
    }

    /**
     * @throws PuidNotUniqueMultipleException
     */
    public function checkDuplicatesInPlatformPuids(array $puids_to_check, int $platform_id): void
    {
        $duplicates = PlatformPuid::query()
            ->where('platform_id', $platform_id)
            ->whereIn('puid', $puids_to_check)
            ->pluck('puid')
            ->toArray();
        $this->doWeHaveDuplicates($duplicates);
    }

    /**
     * @throws PuidNotUniqueMultipleException
     */
    public function checkDuplicatesInCache(array $puids, int $platform_id): void
    {
        $keys = array_map(fn ($puid) => $this->getCacheKey($platform_id, $puid), $puids);
        $cached = Cache::many($keys);

        $duplicates = [];
        foreach ($puids as $puid) {
            $key = $this->getCacheKey($platform_id, $puid);
            if (! empty($cached[$key])) {
                $duplicates[] = $puid;
            }
        }

        $this->doWeHaveDuplicates($duplicates);
    }

    /**
     * @throws PuidNotUniqueMultipleException
     */
    private function doWeHaveDuplicates($duplicates): void
    {
        if (! empty($duplicates)) {
            throw new PuidNotUniqueMultipleException($duplicates);
        }
    }

    public function refreshPuidsInCache(array $puids, int $platform_id): void
    {
        $cacheData = [];
        foreach ($puids as $puid) {
            $cacheData[$this->getCacheKey($platform_id, $puid)] = true;
        }
        Cache::putMany($cacheData, now()->addDays($this->cache_valid_days));
    }

    /**
     * Bulk add PUIDs to database using a single insert query.
     *
     * @param  array<array{platform_id: int, puid: string}>  $statements
     */
    public function addPuidsToDatabase(array $statements): void
    {
        $records = array_map(static fn ($s) => [
            'platform_id' => $s['platform_id'],
            'puid' => $s['puid'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $statements);

        PlatformPuid::insertOrIgnore($records);
    }

    /**
     * Bulk add PUIDs to cache using a single operation.
     *
     * @param  array<array{platform_id: int, puid: string}>  $statements
     */
    public function addPuidsToCache(array $statements): void
    {
        $cacheData = [];
        foreach ($statements as $statement) {
            $cacheData[$this->getCacheKey($statement['platform_id'], $statement['puid'])] = true;
        }
        Cache::putMany($cacheData, now()->addDays($this->cache_valid_days));
    }

    public function checkPuidExists(int $platformId, string $puid): bool
    {
        return $this->isPuidInCache($platformId, $puid) || $this->isPuidInDb($platformId, $puid);
    }
}
