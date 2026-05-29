<?php

namespace Tests\Feature\Services;

use App\Exceptions\PuidNotUniqueMultipleException;
use App\Exceptions\PuidNotUniqueSingleException;
use App\Models\PlatformPuid;
use App\Services\PlatformUniqueIdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlatformUniqueIdServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected PlatformUniqueIdService $platformUniqueIdService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->platformUniqueIdService = app(PlatformUniqueIdService::class);
        $this->assertNotNull($this->platformUniqueIdService);
    }

    /**
     * @throws PuidNotUniqueSingleException
     */
    #[Test]
    public function it_should_store_and_check_cache(): void
    {
        $puid = 'foo-bar-puid';
        $platform_id = 1;

        $this->assertFalse($this->platformUniqueIdService->isPuidInCache($platform_id, $puid));
        $this->platformUniqueIdService->addPuidToCache($platform_id, $puid);
        $this->assertTrue($this->platformUniqueIdService->isPuidInCache($platform_id, $puid));
    }

    /**
     * @throws PuidNotUniqueSingleException
     */
    #[Test]
    public function it_should_store_and_check_database_only_once(): void
    {
        $puid = 'foo-bar-puid';
        $platform_id = 1;

        $this->assertDatabaseCount(PlatformPuid::class, 0);
        $this->platformUniqueIdService->addPuidToDatabase($platform_id, $puid);
        $this->assertDatabaseCount(PlatformPuid::class, 1);
        $this->expectException(PuidNotUniqueSingleException::class);
        $this->platformUniqueIdService->addPuidToDatabase($platform_id, $puid);
        $this->assertDatabaseCount(PlatformPuid::class, 1);
        $this->assertTrue($this->platformUniqueIdService->isPuidInCache($platform_id, $puid));
    }

    /**
     * @throws PuidNotUniqueSingleException
     */
    #[Test]
    public function it_should_refresh_the_cache(): void
    {
        $platform_id = 1;

        $this->platformUniqueIdService->addPuidToCache($platform_id, 'puid-1');
        $this->platformUniqueIdService->addPuidToCache($platform_id, 'puid-2');
        $this->platformUniqueIdService->addPuidToCache($platform_id, 'puid-4');

        $puids = ['puid-1', 'puid-2', 'puid-3', 'puid-4'];
        $this->platformUniqueIdService->refreshPuidsInCache($puids, $platform_id);

        foreach ($puids as $puid) {
            $this->assertTrue($this->platformUniqueIdService->isPuidInCache($platform_id, $puid));
        }
    }

    #[Test]
    public function it_should_store_puid_on_handle(): void
    {
        $puid = $this->faker->uuid();
        $platform_id = 1;
        $key = $this->platformUniqueIdService->getCacheKey($platform_id, $puid);

        $this->assertFalse(Cache::has($key));
        $this->platformUniqueIdService->handlePuid($puid, $platform_id);
        $this->assertTrue(Cache::has($key));

        $this->assertDatabaseHas(PlatformPuid::class, [
            'platform_id' => $platform_id,
            'puid' => $puid,
        ]);
    }

    #[Test]
    public function it_should_throw_unique_exception_on_duplicate_puid_on_handle()
    {
        $puid = $this->faker->uuid();
        $platform_id = 1;
        $key = $this->platformUniqueIdService->getCacheKey($platform_id, $puid);

        $this->assertFalse(Cache::has($key));
        $this->platformUniqueIdService->handlePuid($puid, $platform_id);
        $this->assertTrue(Cache::has($key));

        $this->expectException(PuidNotUniqueSingleException::class);
        $this->platformUniqueIdService->handlePuid($puid, $platform_id);
    }

    #[Test]
    public function it_detects_duplicate_puids_from_cache(): void
    {
        $platform_id = 1;

        $this->platformUniqueIdService->refreshPuidsInCache(['cached-puid'], $platform_id);

        try {
            $this->platformUniqueIdService->checkDuplicatesInCache(['cached-puid', 'missing-puid'], $platform_id);
            $this->fail('Expected cached duplicate PUID detection to throw.');
        } catch (PuidNotUniqueMultipleException $exception) {
            $this->assertSame(['cached-puid'], $exception->getDuplicates());
        }
    }

    #[Test]
    public function it_allows_puids_that_are_not_in_cache(): void
    {
        $this->platformUniqueIdService->checkDuplicatesInCache(['new-puid-1', 'new-puid-2'], 1);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_bulk_adds_puids_to_database_and_ignores_existing_records(): void
    {
        PlatformPuid::create([
            'platform_id' => 1,
            'puid' => 'existing-puid',
        ]);

        $this->platformUniqueIdService->addPuidsToDatabase([
            ['platform_id' => 1, 'puid' => 'existing-puid'],
            ['platform_id' => 1, 'puid' => 'new-puid'],
            ['platform_id' => 2, 'puid' => 'existing-puid'],
        ]);

        $this->assertDatabaseCount(PlatformPuid::class, 3);
        $this->assertDatabaseHas(PlatformPuid::class, [
            'platform_id' => 1,
            'puid' => 'new-puid',
        ]);
        $this->assertDatabaseHas(PlatformPuid::class, [
            'platform_id' => 2,
            'puid' => 'existing-puid',
        ]);
    }

    #[Test]
    public function it_bulk_adds_puids_to_cache(): void
    {
        $statements = [
            ['platform_id' => 1, 'puid' => 'cached-bulk-puid-1'],
            ['platform_id' => 2, 'puid' => 'cached-bulk-puid-2'],
        ];

        $this->platformUniqueIdService->addPuidsToCache($statements);

        foreach ($statements as $statement) {
            $this->assertTrue(
                Cache::has($this->platformUniqueIdService->getCacheKey($statement['platform_id'], $statement['puid']))
            );
        }
    }
}
