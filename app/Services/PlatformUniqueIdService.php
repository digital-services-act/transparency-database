<?php

namespace App\Services;

use App\Exceptions\PuidNotUniqueMultipleException;
use App\Exceptions\PuidNotUniqueSingleException;
use App\Models\PlatformPuid;
use Illuminate\Support\Facades\Cache;

class PlatformUniqueIdService
{
    public function __construct(protected int $cache_valid_days = 2) {}

    public function getCacheKey(int $platform_id, mixed $puid): string
    {
        return 'puid-'.$platform_id.'-'.$puid;
    }

    /**
     * @throws PuidNotUniqueSingleException
     */
    public function addPuidToCache($platform_id, $puid): void
    {
        if ($this->isPuidInCache($platform_id, $puid)) {
            throw new PuidNotUniqueSingleException($puid);
        }

        Cache::put($this->getCacheKey($platform_id, $puid), true, now()->addDays($this->cache_valid_days));
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
        $duplicates = [];
        foreach ($puids as $puid) {
            if ($this->isPuidInCache($platform_id, $puid)) {
                // If the value is not valid, return early
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
        foreach ($puids as $puid) {
            Cache::put($this->getCacheKey($platform_id, $puid), true, now()->addDays($this->cache_valid_days));
        }
    }
}
