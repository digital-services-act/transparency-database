<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexSettingsRefreshIntervalTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_updates_refresh_interval(): void
    {
        ElasticMocker::fake()->refreshIntervalUpdateSucceeds('test_index', '1s');

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'test_index',
            'interval' => '30',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_update(): void
    {
        ElasticMocker::fake()->refreshIntervalUpdateSucceeds('test_index', '1s', false);

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'test_index',
            'interval' => '60',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'nonexistent_index',
            'interval' => '15',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'error_index',
            'interval' => '5',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_zero_interval(): void
    {
        ElasticMocker::fake()->refreshIntervalUpdateSucceeds('test_index', '30s');

        $this->artisan('elasticsearch:index-settings-refresh-interval', [
            'index' => 'test_index',
            'interval' => '0',
        ])
            ->assertExitCode(0);
    }
}
