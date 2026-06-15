<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexBulkModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_enables_bulk_mode_with_default_settings(): void
    {
        ElasticMocker::fake()->bulkModeUpdateSucceeds('statements_index', [
            'number_of_replicas' => '2',
            'refresh_interval' => '1s',
            'translog' => ['durability' => 'request'],
        ]);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
        ])
            ->expectsOutputToContain('Bulk indexing mode enabled')
            ->expectsOutputToContain('Translog durability is async')
            ->assertExitCode(0);
    }

    public function test_command_can_enable_bulk_mode_without_async_translog(): void
    {
        ElasticMocker::fake()->bulkModeUpdateSucceeds('statements_index', [
            'number_of_replicas' => '2',
            'refresh_interval' => '1s',
            'translog' => ['durability' => 'request'],
        ]);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
            '--sync-translog' => true,
        ])
            ->expectsOutputToContain('Bulk indexing mode enabled')
            ->assertExitCode(0);
    }

    public function test_command_disables_bulk_mode_with_restore_options(): void
    {
        ElasticMocker::fake()->bulkModeUpdateSucceeds('statements_index', [
            'number_of_replicas' => '0',
            'refresh_interval' => '-1',
            'translog' => ['durability' => 'async'],
        ]);

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
        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
            'mode' => 'maybe',
        ])
            ->expectsOutput("Mode must be 'on' or 'off'.")
            ->assertExitCode(1);
    }

    public function test_command_rejects_negative_restore_replicas(): void
    {
        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
            '--replicas' => -1,
        ])
            ->expectsOutput('The --replicas option must be 0 or greater.')
            ->assertExitCode(1);
    }

    public function test_command_rejects_empty_restore_refresh_interval(): void
    {
        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
            '--refresh-interval' => '',
        ])
            ->expectsOutput('The --refresh-interval option cannot be empty.')
            ->assertExitCode(1);
    }

    public function test_command_fails_when_bulk_mode_update_is_not_acknowledged(): void
    {
        ElasticMocker::fake()->bulkModeUpdateSucceeds('statements_index', [
            'number_of_replicas' => '2',
            'refresh_interval' => '1s',
            'translog' => ['durability' => 'request'],
        ], false);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
        ])
            ->expectsOutput('Bulk indexing mode update was not acknowledged by Elasticsearch.')
            ->assertExitCode(1);
    }

    public function test_command_reports_refresh_after_disabling_bulk_mode(): void
    {
        ElasticMocker::fake()->bulkModeUpdateSucceeds('statements_index', [
            'number_of_replicas' => '0',
            'refresh_interval' => '-1',
            'translog' => ['durability' => 'async'],
        ], refresh: true);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
            'mode' => 'off',
        ])
            ->expectsOutputToContain('Bulk indexing mode disabled')
            ->expectsOutput('Index refreshed after restoring normal indexing settings.')
            ->assertExitCode(0);
    }

    public function test_command_handles_missing_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'missing_index',
        ])
            ->expectsOutput("Index 'missing_index' does not exist.")
            ->assertExitCode(1);
    }

    public function test_command_handles_runtime_failures(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timed out'));

        $this->artisan('elasticsearch:index-bulk-mode', [
            'index' => 'statements_index',
        ])
            ->expectsOutput("Failed to update bulk indexing mode for index 'statements_index': Connection timed out")
            ->assertExitCode(1);
    }
}
