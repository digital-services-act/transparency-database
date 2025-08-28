<?php

namespace Tests\Feature\Models;

use App\Models\Platform;
use App\Models\PlatformPuid;
use App\Services\DayArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PlatformPuidTest extends TestCase
{
    use RefreshDatabase;

    protected DayArchiveService $day_archive_service;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->day_archive_service = app(DayArchiveService::class);
        $this->assertNotNull($this->day_archive_service);
    }

    public function test_can_create_platform_puid()
    {
        $platform = Platform::factory()->create();
        $platformPuid = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'puid' => 'test-puid-123',
        ]);

        $this->assertDatabaseHas('platform_puids', [
            'id' => $platformPuid->id,
            'platform_id' => $platform->id,
            'puid' => 'test-puid-123',
        ]);
    }

    public function test_fillable_attributes()
    {
        $platformPuid = new PlatformPuid;
        $fillable = ['platform_id', 'puid'];

        $this->assertEquals($fillable, $platformPuid->getFillable());
    }

    public function test_casts_attributes()
    {
        $platformPuid = new PlatformPuid;
        $expectedCasts = ['id' => 'integer'];

        $this->assertEquals($expectedCasts, $platformPuid->getCasts());
    }

    public function test_platform_relationship()
    {
        $platform = Platform::factory()->create();
        $platformPuid = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
        ]);

        $this->assertInstanceOf(Platform::class, $platformPuid->platform);
        $this->assertEquals($platform->id, $platformPuid->platform->id);
    }

    public function test_factory_creates_valid_instance()
    {
        $platformPuid = PlatformPuid::factory()->create();

        $this->assertInstanceOf(PlatformPuid::class, $platformPuid);
        $this->assertNotNull($platformPuid->platform_id);
        $this->assertNotNull($platformPuid->puid);
        $this->assertEquals(500, strlen($platformPuid->puid));
    }

    public function test_can_update_platform_puid()
    {
        $platformPuid = PlatformPuid::factory()->create();
        $newPuid = 'updated-puid-456';

        $platformPuid->update(['puid' => $newPuid]);
        $platformPuid->refresh();

        $this->assertEquals($newPuid, $platformPuid->puid);
    }

    public function test_can_delete_platform_puid()
    {
        $platformPuid = PlatformPuid::factory()->create();
        $id = $platformPuid->id;

        $platformPuid->delete();

        $this->assertDatabaseMissing('platform_puids', ['id' => $id]);
    }

    public function test_platform_puid_has_timestamps()
    {
        $platformPuid = PlatformPuid::factory()->create();

        $this->assertNotNull($platformPuid->created_at);
        $this->assertNotNull($platformPuid->updated_at);
    }

    public function test_can_mass_assign_attributes()
    {
        $platform = Platform::factory()->create();
        $attributes = [
            'platform_id' => $platform->id,
            'puid' => 'mass-assigned-puid',
        ];

        $platformPuid = PlatformPuid::create($attributes);

        $this->assertEquals($attributes['platform_id'], $platformPuid->platform_id);
        $this->assertEquals($attributes['puid'], $platformPuid->puid);
    }

    public function test_puid_max_length()
    {
        $platform = Platform::factory()->create();
        $longPuid = str_repeat('a', 500);

        $platformPuid = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'puid' => $longPuid,
        ]);

        $this->assertEquals(500, strlen($platformPuid->puid));
        $this->assertEquals($longPuid, $platformPuid->puid);
    }

    public function test_can_find_by_platform_and_puid()
    {
        $platform = Platform::factory()->create();
        $puid = 'unique-puid-789';

        PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'puid' => $puid,
        ]);

        $found = PlatformPuid::where('platform_id', $platform->id)
            ->where('puid', $puid)
            ->first();

        $this->assertNotNull($found);
        $this->assertEquals($platform->id, $found->platform_id);
        $this->assertEquals($puid, $found->puid);
    }

    public function test_gets_the_first_platform_puid_id_from_date()
    {
        $platform = Platform::factory()->create();

        $platformPuid1 = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'created_at' => '2030-01-01 00:00:00',
        ]);

        $platformPuid2 = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'created_at' => '2030-01-01 00:00:00',
        ]);

        $this->assertLessThan($platformPuid2->id, $platformPuid1->id);

        $first_id = $this->day_archive_service->getFirstPlatformPuidIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($platformPuid1->id, $first_id);
    }

    public function test_gets_false_on_first_platform_puid_when_no_data()
    {
        $first_id = $this->day_archive_service->getFirstPlatformPuidIdOfDate(Carbon::createFromDate(2030, 1, 1));
        $this->assertFalse($first_id);
    }

    public function test_gets_the_first_platform_puid_id_from_date_in_the_first_minute()
    {
        $platform = Platform::factory()->create();

        $platformPuid1 = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'created_at' => '2030-01-01 00:00:05',
        ]);

        $platformPuid2 = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'created_at' => '2030-01-01 00:00:10',
        ]);

        $first_id = $this->day_archive_service->getFirstPlatformPuidIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($platformPuid1->id, $first_id);
    }

    public function test_gets_the_last_platform_puid_id_from_date()
    {
        $platform = Platform::factory()->create();

        $platformPuid1 = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'created_at' => '2030-01-01 23:59:59',
        ]);

        $platformPuid2 = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'created_at' => '2030-01-01 23:59:59',
        ]);

        $this->assertLessThan($platformPuid2->id, $platformPuid1->id);
        $last_id = $this->day_archive_service->getLastPlatformPuidIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($platformPuid2->id, $last_id);
    }

    public function test_gets_false_on_last_platform_puid_when_no_data()
    {
        $last_id = $this->day_archive_service->getLastPlatformPuidIdOfDate(Carbon::createFromDate(2030, 1, 1));
        $this->assertFalse($last_id);
    }

    public function test_gets_the_last_platform_puid_id_from_date_in_the_last_minute()
    {
        $platform = Platform::factory()->create();

        $platformPuid1 = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'created_at' => '2030-01-01 23:59:45',
        ]);

        $platformPuid2 = PlatformPuid::factory()->create([
            'platform_id' => $platform->id,
            'created_at' => '2030-01-01 23:59:45',
        ]);

        $last_id = $this->day_archive_service->getLastPlatformPuidIdOfDate(Carbon::createFromDate(2030, 1, 1));

        $this->assertEquals($platformPuid2->id, $last_id);
    }
}
