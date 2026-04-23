<?php

namespace Tests\Feature\Commands;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Models\DatabaseVelocity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackDatabaseVelocityTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_a_velocity_record(): void
    {
        $this->artisan('statements:track-velocity')
            ->expectsOutputToContain('Recorded:')
            ->assertExitCode(0);

        $this->assertDatabaseCount('database_velocities', 1);

        $velocity = DatabaseVelocity::first();
        $this->assertNotNull($velocity->max_statement_id);
        $this->assertNotNull($velocity->rows_per_second);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_records_correct_max_id(): void
    {
        $this->artisan('statements:track-velocity')->assertExitCode(0);

        $velocity = DatabaseVelocity::first();
        $this->assertGreaterThanOrEqual(0, $velocity->max_statement_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function the_velocity_page_requires_authentication(): void
    {
        $response = $this->withoutMiddleware(PreventRequestsDuringMaintenance::class)
            ->get(route('database-velocity.index'));
        $response->assertRedirect();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function the_velocity_page_loads_for_admin_users(): void
    {
        $this->signInAsAdmin();

        $response = $this->withoutMiddleware(PreventRequestsDuringMaintenance::class)
            ->get(route('database-velocity.index'));
        $response->assertStatus(200);
        $response->assertSee('Database Velocity');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function the_velocity_page_displays_recorded_data(): void
    {
        $this->signInAsAdmin();

        DatabaseVelocity::factory()->count(5)->create();

        $response = $this->withoutMiddleware(PreventRequestsDuringMaintenance::class)
            ->get(route('database-velocity.index'));
        $response->assertStatus(200);
        $response->assertSee('Rows per Second');
    }
}
