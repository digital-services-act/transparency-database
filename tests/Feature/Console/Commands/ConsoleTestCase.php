<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\CreatesApplication;
use App\Console\Commands\Heartbeat;

abstract class ConsoleTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up a basic testing database configuration
        $this->app['config']->set('database.default', 'testing');
        $this->app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        // Disable output coloring for tests
        $this->withoutMockingConsoleOutput();
        $this->app['config']->set('terminal.ansi', false);

        // Register the Heartbeat command
        $this->app->singleton('command.heartbeat', function ($app) {
            return new Heartbeat();
        });
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)
            ->registerCommand($this->app->make('command.heartbeat'));
    }
}
