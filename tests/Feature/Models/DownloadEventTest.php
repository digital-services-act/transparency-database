<?php

namespace Tests\Feature\Models;

use App\Models\DayArchive;
use App\Models\DownloadEvent;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_belongs_to_a_day_archive_and_platform(): void
    {
        $platform = Platform::factory()->create();
        $dayArchive = DayArchive::factory()->forPlatform($platform)->create();
        $event = DownloadEvent::factory()->create([
            'day_archive_id' => $dayArchive->id,
            'platform_id' => $platform->id,
        ]);

        $this->assertTrue($event->dayArchive->is($dayArchive));
        $this->assertTrue($event->platform->is($platform));
    }
}
