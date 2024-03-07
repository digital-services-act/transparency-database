<?php

namespace App\Services;

use App\Exceptions\PuidNotUniqueMultipleException;
use App\Exceptions\PuidNotUniqueSingleException;
use App\Http\Controllers\Api\v1\StatementAPIController;
use App\Http\Controllers\Api\v1\StatementMultipleAPIController;
use App\Models\ArchivedStatement;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Intl\Countries;

class PlatformUniqueIdService
{
    protected int $cache_valid_days;

    /**
     * @param int $cache_valid_days
     */
    public function __construct(int $cache_valid_days = 2)
    {
        $this->cache_valid_days = $cache_valid_days;
    }

    public function getCacheKey($platform_id, $puid)
    {
        return 'puid-' . $platform_id . '-' . $puid;
    }

    /**
     * @param $platform_id
     * @param $puid
     * @return void
     * @throws PuidNotUniqueSingleException
     */
    public function addPuidToCache($platform_id, $puid): void
    {
        if($this->isPuidInCache($platform_id, $puid)){
            throw new PuidNotUniqueSingleException($puid);
        }
        Cache::put($this->getCacheKey($platform_id, $puid), true, now()->addDays($this->cache_valid_days));
    }

    /**
     * @param $platform_id
     * @param $puid
     * @return void
     * @throws PuidNotUniqueSingleException
     */
    public function addPuidToDatabase($platform_id, $puid): void
    {
        if (ArchivedStatement::where('platform_id', $platform_id)->where('puid', $puid)->exists()) {
            throw new PuidNotUniqueSingleException($puid);
        }
        ArchivedStatement::create([
            'puid' => $puid,
            'date_received' => Carbon::now(),
            'platform_id' => $platform_id
        ]);
    }

    /**
     * Check if the platform identifiers are all unique.
     *
     * @param array $puids
     * @return boolean
     * @throws PuidNotUniqueMultipleException
     */
    public function checkDuplicatesInRequest(array $puids): bool
    {
        $uniquePuids = array_unique($puids);

        if (count($uniquePuids) !== count($puids)) {
            $counts = array_count_values($puids);
            $duplicates = array_filter($counts, function ($count) {
                return $count > 1;
            });
            $duplicates = array_keys($duplicates);

            throw new PuidNotUniqueMultipleException($duplicates);
        }

        return true;
    }

    /**
     * @param int|null $platform_id
     * @param mixed $puid
     * @return bool
     */
    public function isPuidInCache(
        ?int $platform_id,
        mixed $puid
    ): bool {
        return Cache::get($this->getCacheKey($platform_id, $puid), false);
    }

    /**
     * @throws PuidNotUniqueMultipleException
     */
    public function checkDuplicatesInArchivedStatement(array $puids_to_check, ?int $platform_id)
    {
        $duplicates = ArchivedStatement::query()->where('platform_id', $platform_id)->whereIn('puid',
            $puids_to_check)->pluck('puid')->toArray();
        $this->doWeHaveDuplicates($duplicates);
    }

    /**
     * @throws PuidNotUniqueMultipleException
     */
    public function checkDuplicatesInCache(array $puids, $platform_id)
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

    private function doWeHaveDuplicates($duplicates)
    {
        if (!empty($duplicates)) {
            throw new PuidNotUniqueMultipleException($duplicates);
        }
    }

    public function refreshPuidsInCache(array $puids, $platform_id)
    {
        foreach ($puids as $puid) {
            Cache::put($this->getCacheKey($platform_id, $puid), true, now()->addDays($this->cache_valid_days));
        }
    }
}
