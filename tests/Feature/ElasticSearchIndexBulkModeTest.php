<?php

namespace Tests\Feature;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ElasticSearchIndexBulkModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_enables_bulk_mode_with_default_settings(): void
    {
        $result = [
            'index' => 'statements_index',
            'enabled' => true,
            'previous_settings' => [
                'number_of_replicas' => '2',
                'refresh_interval' => '1s',
                'translog.durability' => 'request',
            ],
            'new_settings' => [
                'number_of_replicas' => 0,
                'refresh_interval' => '-1',
                'translog.durability' => 'async',
            ],
            'updated' => true,
            'acknowledged' => true,
            'refreshed' => false,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexBulkMode')
            ->with('statements_index', true, 2, '1s', true, true)
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
        ])
            ->expectsOutputToContain('Bulk indexing mode enabled')
            ->expectsOutputToContain('Translog durability is async')
            ->assertExitCode(0);
    }

    public function test_command_can_enable_bulk_mode_without_async_translog(): void
    {
        $result = [
            'index' => 'statements_index',
            'enabled' => true,
            'previous_settings' => [
                'number_of_replicas' => '2',
                'refresh_interval' => '1s',
                'translog.durability' => 'request',
            ],
            'new_settings' => [
                'number_of_replicas' => 0,
                'refresh_interval' => '-1',
                'translog.durability' => 'request',
            ],
            'updated' => true,
            'acknowledged' => true,
            'refreshed' => false,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexBulkMode')
            ->with('statements_index', true, 2, '1s', false, true)
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
            '--sync-translog' => true,
        ])
            ->expectsOutputToContain('Bulk indexing mode enabled')
            ->assertExitCode(0);
    }

    public function test_command_disables_bulk_mode_with_restore_options(): void
    {
        $result = [
            'index' => 'statements_index',
            'enabled' => false,
            'previous_settings' => [
                'number_of_replicas' => '0',
                'refresh_interval' => '-1',
                'translog.durability' => 'async',
            ],
            'new_settings' => [
                'number_of_replicas' => 3,
                'refresh_interval' => '5s',
                'translog.durability' => 'request',
            ],
            'updated' => true,
            'acknowledged' => true,
            'refreshed' => false,
        ];

        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexBulkMode')
            ->with('statements_index', false, 3, '5s', true, false)
            ->andReturn($result);

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
            'mode' => 'off',
            '--replicas' => 3,
            '--refresh-interval' => '5s',
            '--skip-refresh' => true,
        ])
            ->expectsOutputToContain('Bulk indexing mode disabled')
            ->assertExitCode(0);
    }

    public function test_command_rejects_invalid_mode(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldNotReceive('updateIndexBulkMode');

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
            'mode' => 'maybe',
        ])
            ->expectsOutput("Mode must be 'on' or 'off'.")
            ->assertExitCode(1);
    }

    public function test_command_handles_missing_index(): void
    {
        $mockService = Mockery::mock(StatementElasticSearchService::class);
        $mockService->shouldReceive('updateIndexBulkMode')
            ->with('missing_index', true, 2, '1s', true, true)
            ->andThrow(new RuntimeException('Index does not exist'));

        $this->app->instance(StatementElasticSearchService::class, $mockService);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'missing_index',
        ])
            ->expectsOutput("Index 'missing_index' does not exist.")
            ->assertExitCode(1);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
