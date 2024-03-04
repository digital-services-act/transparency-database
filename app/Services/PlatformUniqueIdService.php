<?php

namespace App\Services;

use App\Exceptions\PlatformUniqueIdentifierNotUnique;
use App\Http\Controllers\Api\v1\StatementAPIController;
use App\Http\Controllers\Api\v1\StatementMultipleAPIController;
use App\Models\ArchivedStatement;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Intl\Countries;

class PlatformUniqueIdService
{
    public function getCacheKey($platform_id, $puid){
        return 'puid-' . $platform_id . '-' . $puid;
    }

    /**
     * @param $platform_id
     * @param $puid
     * @return boolean
     */
    public function addPuidToCache($platform_id, $puid): bool
    {
        $cache_valid_days = 7;
        $key = $this->getCacheKey($platform_id, $puid);
        return Cache::add($key, 0, now()->addDays($cache_valid_days));
    }

    /**
     * @param $platform_id
     * @param $puid
     * @return ArchivedStatement
     */
    public function addPuidToDatabase($platform_id, $puid): ArchivedStatement
    {
        return ArchivedStatement::create([
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
     * @throws PlatformUniqueIdentifierNotUnique
     */
    public function getDuplicatesFromRequest(array $puids): bool
    {
        $uniquePuids = array_unique($puids);

        if (count($uniquePuids) !== count($puids)) {

            $counts = array_count_values($puids);
            $duplicates = array_filter($counts, function ($count) {
                return $count > 1;
            });
            $duplicates = array_keys($duplicates);

            throw new PlatformUniqueIdentifierNotUnique($duplicates);
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
        if (Cache::has($this->getCacheKey($platform_id, $puid))) {
            return true;
        }
        return false;
    }

    /**
     * @throws PlatformUniqueIdentifierNotUnique
     */
    public function getDuplicatesFromArchivedStatement(array $puids_to_check, ?int $platform_id)
    {
        $duplicates =  ArchivedStatement::query()->where('platform_id', $platform_id)->whereIn('puid',
            $puids_to_check)->pluck('puid')->toArray();
        $this->doWeHaveDuplicates($duplicates);
    }

    /**
     * @throws PlatformUniqueIdentifierNotUnique
     */
    public function getDuplicatesFromCache(array $puids, $platform_id)
    {
        $duplicates = [];
        foreach ($puids as $puid) {
            if ($this->isPuidInCache($platform_id, $puid, $this)) {
                // If the value is not valid, return early
                $duplicates[] = $puid;
            }
        }
        $this->doWeHaveDuplicates($duplicates);
    }

    private function doWeHaveDuplicates($duplicates){
        if (!empty($duplicates)) {
            throw new PlatformUniqueIdentifierNotUnique($duplicates);
        }
    }
}
