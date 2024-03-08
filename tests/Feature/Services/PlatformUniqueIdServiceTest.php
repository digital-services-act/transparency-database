<?php

namespace Tests\Feature\Services;

use App\Exceptions\PuidNotUniqueSingleException;
use App\Models\ArchivedStatement;
use App\Models\DayArchive;
use App\Models\Statement;
use App\Services\DayArchiveService;
use App\Services\PlatformUniqueIdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;

class PlatformUniqueIdServiceTest extends TestCase
{

    use RefreshDatabase;

    protected PlatformUniqueIdService $platformUniqueIdService;

    #[\Override] protected function setUp(): void
    {
        parent::setUp();
        $this->platformUniqueIdService = app(PlatformUniqueIdService::class);
        $this->assertNotNull($this->platformUniqueIdService);
    }

    /**
     * @test
     */
    public function it_should_store_and_check_cache(): void
    {
        $puid = "foo-bar-puid";
        $platform_id = 1;

        $this->assertFalse($this->platformUniqueIdService->isPuidInCache($platform_id, $puid));

        $this->platformUniqueIdService->addPuidToCache($platform_id, $puid);

        $this->assertTrue($this->platformUniqueIdService->isPuidInCache($platform_id, $puid));
    }

    /**
     * @test
     */
    public function it_should_store_and_check_database_only_once(): void
    {
        $puid = "foo-bar-puid";
        $platform_id = 1;

        $this->assertDatabaseCount(ArchivedStatement::class, 0);

        $this->platformUniqueIdService->addPuidToDatabase($platform_id, $puid);

        $this->assertDatabaseCount(ArchivedStatement::class, 1);

        $this->expectException(PuidNotUniqueSingleException::class);

        $this->platformUniqueIdService->addPuidToDatabase($platform_id, $puid);

        $this->assertDatabaseCount(ArchivedStatement::class, 1);

        $this->assertTrue($this->platformUniqueIdService->isPuidInCache($platform_id, $puid));
    }

    /**
     * @test
     */
    public function it_should_refresh_the_cache(): void
    {

        $platform_id = 1;

        $this->platformUniqueIdService->addPuidToCache($platform_id,'puid-1');
        $this->platformUniqueIdService->addPuidToCache($platform_id,'puid-2');
        $this->platformUniqueIdService->addPuidToCache($platform_id,'puid-4');

        $puids = ['puid-1','puid-2','puid-3','puid-4'];
        $this->platformUniqueIdService->refreshPuidsInCache($puids,$platform_id);

        foreach($puids as $puid){
            $this->assertTrue($this->platformUniqueIdService->isPuidInCache($platform_id,$puid));
        }



    }

}
