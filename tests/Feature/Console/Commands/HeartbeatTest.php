<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Console\Commands\ConsoleTestCase;
use Mockery;

class HeartbeatTest extends ConsoleTestCase
{
    protected function preventDatabaseSeeding()
    {
        // Use a method to prevent database seeding instead of trying to set an undefined property
        $this->app['config']->set('database.default', 'testing');
        $this->app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    /** @test */
    public function it_reports_when_database_is_up()
    {
        // Create a mock query builder
        $queryBuilder = Mockery::mock('Illuminate\Database\Query\Builder');
        $queryBuilder->shouldReceive('select')->once()->with(1)->andReturnSelf();
        $queryBuilder->shouldReceive('take')->once()->with(1)->andReturnSelf();
        $queryBuilder->shouldReceive('get')->once()->andReturn(['result']);

        // Mock DB facade
        DB::shouldReceive('raw')->once()->with(1)->andReturn(1);
        DB::shouldReceive('table')
            ->once()
            ->with('statements')
            ->andReturn($queryBuilder);

        // Execute the command
        $this->withoutMockingConsoleOutput();
        Artisan::call('heartbeat');

        $this->assertEquals('Database is up.', trim(Artisan::output()));
    }

    /** @test */
    public function it_reports_when_database_is_down()
    {
        // Create a mock query builder that throws an exception
        $queryBuilder = Mockery::mock('Illuminate\Database\Query\Builder');
        $queryBuilder->shouldReceive('select')->once()->with(1)->andReturnSelf();
        $queryBuilder->shouldReceive('take')->once()->with(1)->andReturnSelf();
        $queryBuilder->shouldReceive('get')->once()->andThrow(new \Exception('Could not connect to database'));

        // Mock DB facade
        DB::shouldReceive('raw')->once()->with(1)->andReturn(1);
        DB::shouldReceive('table')
            ->once()
            ->with('statements')
            ->andReturn($queryBuilder);

        // Execute the command
        $this->withoutMockingConsoleOutput();
        Artisan::call('heartbeat');

        $this->assertEquals('Database is down: Could not connect to database', trim(Artisan::output()));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
