<?php

namespace Tests\Feature\Console\Commands;

use App\Models\DayArchive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class DayArchiveTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_without_errors(): void
    {
        // Create a DayArchive record for the command to process
        DayArchive::factory()->create(['date' => '2025-09-03']);

        ElasticMocker::fake()->sqlCountReturns(123);

        // Run the command for the previous day and assert it runs successfully
        $this->artisan('dayarchive:totals 2025-09-04')
            ->assertExitCode(0);
    }

    public function test_it_does_not_save_when_nosave_option_is_used(): void
    {
        // Create a DayArchive record
        $dayArchive = DayArchive::factory()->create(['date' => '2025-09-03', 'total' => 0]);

        ElasticMocker::fake()->sqlCountReturns(123);

        // Run the command with the --nosave option
        $this->artisan('dayarchive:totals 2025-09-04 --nosave')
            ->assertExitCode(0);

        // Assert the 'total' was not updated in the database
        $this->assertDatabaseHas('day_archives', [
            'id' => $dayArchive->id,
            'total' => 0,
        ]);
    }

    public function test_it_handles_archives_with_a_platform_and_saves_the_total(): void
    {
        // Create a DayArchive with a platform
        $dayArchive = DayArchive::factory()->forPlatform()->create([
            'date' => '2025-09-03',
            'total' => 0,
        ]);

        ElasticMocker::fake()->sqlCountReturns(456);

        // Run the command for the previous day
        $this->artisan('dayarchive:totals 2025-09-03')
            ->assertExitCode(0);

        // Assert the 'total' was updated in the database
        $this->assertDatabaseHas('day_archives', [
            'id' => $dayArchive->id,
            'total' => 456,
        ]);
    }
}
