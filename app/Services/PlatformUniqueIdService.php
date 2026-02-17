<?php

namespace App\Services;

use App\Exceptions\PuidNotUniqueMultipleException;
use App\Exceptions\PuidNotUniqueSingleException;
use App\Models\PlatformPuid;
use App\Models\Statement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlatformUniqueIdService
{
    public $lock_valid_seconds = 60;
    public $cacheKeys = [];

    public function __construct(
        protected GroupedSubmissionsService $groupService,
        protected StatementSearchService $opensearch,
        protected int $cache_valid_days = 3
    ) {}

    public function handlePuid(mixed $puid, int $platform_id): void
    {
        $key = $this->getCacheKey($platform_id, $puid);
        $lockKey = "lock:puid:{{$key}}";

        $lock = Cache::lock($lockKey, $this->lock_valid_seconds);

        // @codeCoverageIgnoreStart
        if (! $lock->get()) {
            // Log::info('Lock encountered for PUID ' . $puid . ' on platform ' . $platform_id . ' (single)');
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

    public function handleBatchPayload(array &$payload): array
    {
        $output = [];
        $puids = array_map(
            static fn($potential_statement) => $potential_statement['puid'],
            $payload['statements']
        );

        // Check if PUIDs are unique in the Request made by the client
        $this->checkDuplicatesInRequest($puids);

        $locks = [];
        $failedLocks = [];

        try {
            foreach ($puids as $puid) {
                $key = $this->getCacheKey($payload['platform_id'], $puid);
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

            // Locks encountered => duplicates, abort
            // @codeCoverageIgnoreStart
            if (! empty($failedLocks)) {
                Log::info('Locks encountered for PUIDs ' . implode(', ', $failedLocks) . ' on platform ' . $payload['platform_id'] . ' (multi)');
                throw new PuidNotUniqueMultipleException($failedLocks);
            }
            // @codeCoverageIgnoreEnd

            // Check cache and database for duplicates
            $this->checkDuplicatesInCache($puids, $payload['platform_id']);
            $this->checkDuplicatesInPlatformPuids($puids, $payload['platform_id']);

            // No duplicates, add all PUIDs to cache and db
            $cacheFailures = [];
            foreach ($puids as $puid) {
                try {
                    $this->addPuidToCache($payload['platform_id'], $puid);
                    $this->cacheKeys[] = $this->getCacheKey($payload['platform_id'], $puid);
                    // @codeCoverageIgnoreStart
                } catch (PuidNotUniqueSingleException $e) {
                    $cacheFailures[] = $puid;
                    // @codeCoverageIgnoreEnd
                }
            }

            // @codeCoverageIgnoreStart
            if (! empty($cacheFailures)) {
                // This should not happen, but in case it does, we log it and throw an exception
                Log::warning('PuidNotUniqueSingleException encountered during batch processing when adding PUIDs to Cache and DB:' . implode(', ', $cacheFailures) . ' on platform ' . $payload['platform_id']);
                throw new PuidNotUniqueMultipleException($cacheFailures);
            }
            // @codeCoverageIgnoreEnd

            $statements = $payload['statements'];
            // "Enrich" the payload with additional data
            $output = $this->groupService->enrichThePayloadForBulkInsert(
                $statements,
                $payload['platform_id'],
                $payload['user_id'],
                $payload['method']
            );

            try {
                DB::transaction(function () use ($statements, $puids, $payload) {
                    // Now we save the statements
                    Statement::insertBulk($statements);

                    // Bulk insert PUIDS into DB (should be ok????)
                    PlatformPuid::insertBulk($puids, $payload['platform_id']);
                });
                // @codeCoverageIgnoreStart
            } catch (\Exception $e) {
                // If we have an exception, we need to remove the PUIDs from the cache
                foreach ($puids as $puid) {
                    Cache::forget($this->getCacheKey($payload['platform_id'], $puid));
                }
                throw $e;
                // @codeCoverageIgnoreEnd
            }
        } finally {
            foreach ($locks as $lock) {
                optional($lock)->release();
            }
        }

        return $output;
    }

    public function getCacheKey(int $platform_id, mixed $puid): string
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
        if (! Cache::add($this->getCacheKey($platform_id, $puid), true, now()->addDays($this->cache_valid_days))) {
            throw new PuidNotUniqueSingleException($puid);
        }
    }

    /**
     * @param $platform_id
     * @param $puid
     * @return void
     * @throws PuidNotUniqueSingleException
     */
    public function addPuidToDatabase($platform_id, $puid): void
    {
        if (PlatformPuid::where('platform_id', $platform_id)->where('puid', $puid)->exists()) {
            throw new PuidNotUniqueSingleException($puid);
        }

        PlatformPuid::create([
            'puid' => $puid,
            'platform_id' => $platform_id
        ]);
    }

    /**
     * Check if the platform identifiers are all unique.
     *
     *
     * @return boolean
     * @throws PuidNotUniqueMultipleException
     */
    public function checkDuplicatesInRequest(array $puids): bool
    {
        $uniquePuids = array_unique($puids);

        if (count($uniquePuids) !== count($puids)) {
            $counts = array_count_values($puids);
            $duplicates = array_filter($counts, static fn($count) => $count > 1);
            $duplicates = array_keys($duplicates);

            throw new PuidNotUniqueMultipleException($duplicates);
        }

        return true;
    }

    /**
     *
     * @return bool
     */
    public function isPuidInCache(int $platform_id, mixed $puid): bool
    {
        return Cache::get($this->getCacheKey($platform_id, $puid), false);
    }

    /**
     *
     * @return bool
     */
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
        if (!empty($duplicates)) {
            throw new PuidNotUniqueMultipleException($duplicates);
        }
    }

    public function refreshPuidsInCache(array $puids, int $platform_id): void
    {
        foreach ($puids as $puid) {
            Cache::put($this->getCacheKey($platform_id, $puid), true, now()->addDays($this->cache_valid_days));
        }
    }

    public function checkPuidExists(int $platformId, string $puid): bool
    {
        return $this->isPuidInCache($platformId, $puid) || $this->isPuidInDb($platformId, $puid);
    }
}
