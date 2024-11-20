<?php

namespace Tests\Unit\Factories;

use App\Models\DayArchive;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayArchiveFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_basic_day_archive()
    {
        $archive = DayArchive::factory()->create();

        $this->assertNotNull($archive);
        $this->assertInstanceOf(DayArchive::class, $archive);
        $this->assertNotNull($archive->date);
        $this->assertNotNull($archive->url);
        $this->assertNotNull($archive->urllight);
        $this->assertNull($archive->completed_at);
        $this->assertNull($archive->platform_id);
        $this->assertIsInt($archive->total);
        $this->assertIsInt($archive->size);
        $this->assertIsInt($archive->sizelight);
        $this->assertIsInt($archive->zipsize);
        $this->assertIsInt($archive->ziplightsize);
        $this->assertNotNull($archive->sha1);
        $this->assertNotNull($archive->sha1light);
        $this->assertNotNull($archive->sha1url);
        $this->assertNotNull($archive->sha1urllight);
    }

    public function test_it_creates_completed_archive()
    {
        $archive = DayArchive::factory()->completed()->create();

        $this->assertNotNull($archive->completed_at);
    }

    public function test_it_creates_archive_for_existing_platform()
    {
        $platform = Platform::factory()->create();
        $archive = DayArchive::factory()->forPlatform($platform)->create();

        $this->assertEquals($platform->id, $archive->platform_id);
    }

    public function test_it_creates_archive_with_new_platform()
    {
        $archive = DayArchive::factory()->forPlatform()->create();

        $this->assertNotNull($archive->platform_id);
        $this->assertDatabaseHas('platforms', ['id' => $archive->platform_id]);
    }

    public function test_it_creates_global_archive()
    {
        $archive = DayArchive::factory()->global()->create();

        $this->assertNull($archive->platform_id);
    }

    public function test_it_creates_multiple_archives_with_unique_dates()
    {
        $archives = DayArchive::factory()->count(5)->create();

        $uniqueDates = $archives->pluck('date')->unique()->count();
        $this->assertEquals(5, $uniqueDates, 'Each archive should have a unique date');
    }

    public function test_size_values_are_within_expected_ranges()
    {
        $archive = DayArchive::factory()->create();

        // Full version sizes
        $this->assertGreaterThanOrEqual(1000000, $archive->size); // At least 1MB
        $this->assertLessThanOrEqual(10000000, $archive->size); // At most 10MB
        $this->assertGreaterThanOrEqual(800000, $archive->zipsize); // At least 800KB
        $this->assertLessThanOrEqual(8000000, $archive->zipsize); // At most 8MB

        // Light version sizes
        $this->assertGreaterThanOrEqual(500000, $archive->sizelight); // At least 500KB
        $this->assertLessThanOrEqual(5000000, $archive->sizelight); // At most 5MB
        $this->assertGreaterThanOrEqual(400000, $archive->ziplightsize); // At least 400KB
        $this->assertLessThanOrEqual(4000000, $archive->ziplightsize); // At most 4MB
    }
}
