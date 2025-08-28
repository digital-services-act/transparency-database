<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\Heartbeat;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\CreatesApplication;

class HeartbeatTest extends TestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up a basic testing database configuration
        Config::set('database.default', 'testing');
        Config::set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Register the Heartbeat command
        $this->app->singleton('command.heartbeat', function ($app) {
            return new Heartbeat;
        });
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)
            ->registerCommand($this->app->make('command.heartbeat'));
    }

    /** @test */
    public function it_reports_when_database_is_up()
    {
        // Act & Assert
        $this->artisan('heartbeat')
            ->assertSuccessful()
            ->doesntExpectOutput('Database connection failed')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_reports_when_database_is_down()
    {
        // Arrange - Set an invalid database connection
        Config::set('database.connections.testing.database', '/invalid/database.sqlite');
        DB::purge();

        // Act & Assert
        $this->artisan('heartbeat')
            ->assertFailed()
            ->expectsOutput('Database connection failed')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_respects_custom_timeout()
    {
        // Act & Assert
        $this->artisan('heartbeat', ['--timeout' => 10])
            ->assertSuccessful()
            ->doesntExpectOutput('Database connection failed')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_displays_help_information()
    {
        // Act & Assert
        $this->artisan('heartbeat --help')
            ->assertSuccessful()
            ->expectsOutput('Description:')
            ->expectsOutput('  Check if the database is up by performing a simple query.')
            ->expectsOutput('Usage:')
            ->expectsOutput('  heartbeat [options]')
            ->expectsOutput('Options:')
            ->expectsOutput('      --timeout[=TIMEOUT]  Maximum time in seconds to wait for response [default: "5"]')
            ->assertExitCode(0);
    }
}
