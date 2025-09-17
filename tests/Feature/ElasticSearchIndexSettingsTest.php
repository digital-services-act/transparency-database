<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchIndexSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_displays_comprehensive_index_settings(): void
    {
        $settingsInfo = [
            'index' => 'test_index',
            'settings' => [
                'Basic' => [
                    ['Number of Shards', '5'],
                    ['Number of Replicas', '1'],
                    ['Creation Date', '2024-09-17 10:30:45'],
                    ['UUID', 'abc123-def456-ghi789'],
                    ['Version Created', '8.10.4'],
                ],
                'Refresh' => [
                    ['Refresh Interval', '1s'],
                ],
                'Analysis' => [
                    ['Analyzers', '3 configured'],
                    ['Tokenizers', '2 configured'],
                    ['Filters', '5 configured'],
                ],
                'Advanced' => [
                    ['Max Result Window', '10,000'],
                    ['Max Inner Result Window', '100'],
                ],
            ],
            'raw_settings' => [], // Not used in command output
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexSettings')
            ->with('test_index')
            ->andReturn($settingsInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings', ['index' => 'test_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_minimal_settings(): void
    {
        $settingsInfo = [
            'index' => 'minimal_index',
            'settings' => [
                'Basic' => [
                    ['Number of Shards', '1'],
                    ['Number of Replicas', '0'],
                    ['Creation Date', '2024-09-17 09:15:30'],
                    ['UUID', 'minimal-uuid-123'],
                    ['Version Created', '8.10.4'],
                ],
            ],
            'raw_settings' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexSettings')
            ->with('minimal_index')
            ->andReturn($settingsInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings', ['index' => 'minimal_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_empty_settings(): void
    {
        $settingsInfo = [
            'index' => 'empty_settings_index',
            'settings' => [],
            'raw_settings' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexSettings')
            ->with('empty_settings_index')
            ->andReturn($settingsInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings', ['index' => 'empty_settings_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexSettings')
            ->with('nonexistent_index')
            ->andThrow(new RuntimeException('Index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings', ['index' => 'nonexistent_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_exception(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexSettings')
            ->with('error_index')
            ->andThrow(new RuntimeException('Connection timeout'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings', ['index' => 'error_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_production_index_with_all_settings(): void
    {
        $settingsInfo = [
            'index' => 'statements_production_2024.09.17',
            'settings' => [
                'Basic' => [
                    ['Number of Shards', '10'],
                    ['Number of Replicas', '2'],
                    ['Creation Date', '2024-09-17 08:00:00'],
                    ['UUID', 'prod-uuid-abc123-def456'],
                    ['Version Created', '8.10.4'],
                ],
                'Refresh' => [
                    ['Refresh Interval', '30s'],
                ],
                'Analysis' => [
                    ['Analyzers', '8 configured'],
                    ['Tokenizers', '4 configured'],
                    ['Filters', '12 configured'],
                ],
                'Routing' => [
                    ['Allocation Include', 'data_hot,data_warm'],
                ],
                'Advanced' => [
                    ['Max Result Window', '50,000'],
                    ['Max Inner Result Window', '500'],
                    ['Max Rescore Window', '1,000'],
                ],
            ],
            'raw_settings' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexSettings')
            ->with('statements_production_2024.09.17')
            ->andReturn($settingsInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings', ['index' => 'statements_production_2024.09.17'])
            ->assertExitCode(0);
    }

    public function test_command_handles_development_index(): void
    {
        $settingsInfo = [
            'index' => 'dev_test_index',
            'settings' => [
                'Basic' => [
                    ['Number of Shards', '1'],
                    ['Number of Replicas', '0'],
                    ['Creation Date', '2024-09-17 12:45:15'],
                    ['UUID', 'dev-uuid-789'],
                    ['Version Created', '8.10.4'],
                ],
                'Refresh' => [
                    ['Refresh Interval', '1s'],
                ],
            ],
            'raw_settings' => [],
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('getIndexSettings')
            ->with('dev_test_index')
            ->andReturn($settingsInfo);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-settings', ['index' => 'dev_test_index'])
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
