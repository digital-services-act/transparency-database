<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchIndexSettingsRefreshIntervalTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_updates_refresh_interval(): void
    {
        $result = [
            'index' => 'test_index',
            'previous_interval' => '1s',
            'new_interval' => '30s',
            'updated' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexRefreshInterval')
            ->with('test_index', 30)
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'test_index',
            'interval' => '30',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_update(): void
    {
        $result = [
            'index' => 'test_index',
            'previous_interval' => '1s',
            'new_interval' => '60s',
            'updated' => true,
            'acknowledged' => false,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexRefreshInterval')
            ->with('test_index', 60)
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'test_index',
            'interval' => '60',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexRefreshInterval')
            ->with('nonexistent_index', 15)
            ->andThrow(new RuntimeException('Index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'nonexistent_index',
            'interval' => '15',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexRefreshInterval')
            ->with('error_index', 5)
            ->andThrow(new RuntimeException('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'error_index',
            'interval' => '5',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_zero_interval(): void
    {
        $result = [
            'index' => 'test_index',
            'previous_interval' => '30s',
            'new_interval' => '0s',
            'updated' => true,
            'acknowledged' => true,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexRefreshInterval')
            ->with('test_index', 0)
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'test_index',
            'interval' => '0',
        ])
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
