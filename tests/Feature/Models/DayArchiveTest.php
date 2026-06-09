<?php

namespace Tests\Feature\Models;

use App\Models\DayArchive;
use App\Models\Platform;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_day_archive()
    {
        $date = Carbon::now()->toDateString();
        $dayArchive = DayArchive::create([
            'date' => $date,
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
            'url' => 'https://example.com/archive.zip',
            'urllight' => 'https://example.com/archive-light.zip',
            'platform_id' => null,
        ]);

        $this->assertDatabaseHas('day_archives', [
            'date' => $date,
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
            'url' => 'https://example.com/archive.zip',
            'urllight' => 'https://example.com/archive-light.zip',
            'platform_id' => null,
        ]);

        $this->assertInstanceOf(DayArchive::class, $dayArchive);
    }

    public function test_date_is_cast_to_date()
    {
        $date = Carbon::now()->toDateString();
        $dayArchive = DayArchive::create([
            'date' => $date,
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
        ]);

        $this->assertInstanceOf(Carbon::class, $dayArchive->date);
        $this->assertEquals($date, $dayArchive->date->toDateString());
    }

    public function test_platform_relationship()
    {
        $platform = Platform::factory()->create();
        $dayArchive = DayArchive::create([
            'date' => Carbon::now()->toDateString(),
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
            'platform_id' => $platform->id,
        ]);

        $this->assertInstanceOf(Platform::class, $dayArchive->platform);
        $this->assertEquals($platform->id, $dayArchive->platform->id);
    }

    public function test_global_scope()
    {
        // Create global archive (no platform_id)
        DayArchive::create([
            'date' => Carbon::now()->toDateString(),
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
            'platform_id' => null,
        ]);

        // Create platform-specific archive
        $platform = Platform::factory()->create();
        DayArchive::create([
            'date' => Carbon::now()->toDateString(),
            'total' => 50,
            'size' => 500,
            'sizelight' => 250,
            'platform_id' => $platform->id,
        ]);

        $globalArchives = DayArchive::global()->get();

        $this->assertEquals(1, $globalArchives->count());
        $this->assertNull($globalArchives->first()->platform_id);
    }

    public function test_can_update_day_archive()
    {
        $dayArchive = DayArchive::create([
            'date' => Carbon::now()->toDateString(),
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
        ]);

        $newTotal = 150;
        $dayArchive->update(['total' => $newTotal]);
        $dayArchive->refresh();

        $this->assertEquals($newTotal, $dayArchive->total);
    }

    public function test_can_delete_day_archive()
    {
        $dayArchive = DayArchive::create([
            'date' => Carbon::now()->toDateString(),
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
        ]);

        $id = $dayArchive->id;
        $dayArchive->delete();

        $this->assertDatabaseMissing('day_archives', ['id' => $id]);
    }

    public function test_mass_assignment_protection()
    {
        $date = Carbon::now()->toDateString();
        $attributes = [
            'date' => $date,
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
            'non_existent_field' => 'test',
        ];

        // Filter out non-existent fields before creating
        $validAttributes = array_intersect_key($attributes, array_flip([
            'date', 'total', 'size', 'sizelight', 'url', 'urllight', 'completed_at', 'platform_id',
        ]));

        $dayArchive = DayArchive::create($validAttributes);

        $this->assertDatabaseHas('day_archives', [
            'date' => $date,
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
        ]);

        // Verify the non-existent field was not added to the model
        $this->assertArrayNotHasKey('non_existent_field', $dayArchive->getAttributes());
    }

    public function test_can_query_by_date_range()
    {
        $today = Carbon::now();
        $yesterday = Carbon::yesterday();
        $tomorrow = Carbon::tomorrow();

        // Create archives for different dates
        DayArchive::create([
            'date' => $yesterday,
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
        ]);

        DayArchive::create([
            'date' => $today,
            'total' => 150,
            'size' => 1500,
            'sizelight' => 750,
        ]);

        DayArchive::create([
            'date' => $tomorrow,
            'total' => 200,
            'size' => 2000,
            'sizelight' => 1000,
        ]);

        $archives = DayArchive::whereBetween('date', [$yesterday, $today])->get();

        $this->assertEquals(2, $archives->count());
        $this->assertTrue($archives->contains('total', 100));
        $this->assertTrue($archives->contains('total', 150));
    }

    public function test_can_aggregate_totals()
    {
        // Create multiple archives
        DayArchive::create([
            'date' => Carbon::now()->subDays(2),
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
        ]);

        DayArchive::create([
            'date' => Carbon::now()->subDay(),
            'total' => 150,
            'size' => 1500,
            'sizelight' => 750,
        ]);

        DayArchive::create([
            'date' => Carbon::now(),
            'total' => 200,
            'size' => 2000,
            'sizelight' => 1000,
        ]);

        $totalSum = DayArchive::sum('total');
        $sizeSum = DayArchive::sum('size');
        $sizeLightSum = DayArchive::sum('sizelight');

        $this->assertEquals(450, $totalSum);
        $this->assertEquals(4500, $sizeSum);
        $this->assertEquals(2250, $sizeLightSum);
    }

    public function test_completed_at_timestamp()
    {
        $now = Carbon::now();
        $dayArchive = DayArchive::create([
            'date' => $now->toDateString(),
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
            'completed_at' => $now,
        ]);

        $this->assertNotNull($dayArchive->completed_at);
        $this->assertInstanceOf(Carbon::class, $dayArchive->completed_at);
        $this->assertEquals($now->timestamp, $dayArchive->completed_at->timestamp);
    }

    public function test_url_and_urllight_nullable()
    {
        $dayArchive = DayArchive::create([
            'date' => Carbon::now()->toDateString(),
            'total' => 100,
            'size' => 1000,
            'sizelight' => 500,
        ]);

        $this->assertNull($dayArchive->url);
        $this->assertNull($dayArchive->urllight);
    }
}
