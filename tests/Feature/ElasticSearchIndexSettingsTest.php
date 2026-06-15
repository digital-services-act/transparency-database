<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_displays_comprehensive_index_settings(): void
    {
        ElasticMocker::fake()->indexSettingsReturns('test_index', [
            'index' => [
                'number_of_shards' => '5',
                'number_of_replicas' => '1',
                'creation_date' => 1726569045000,
                'uuid' => 'abc123-def456-ghi789',
                'version' => ['created' => '8.10.4'],
                'refresh_interval' => '1s',
                'analysis' => [
                    'analyzer' => ['a' => [], 'b' => [], 'c' => []],
                    'tokenizer' => ['a' => [], 'b' => []],
                    'filter' => ['a' => [], 'b' => [], 'c' => [], 'd' => [], 'e' => []],
                ],
                'max_result_window' => 10000,
                'max_inner_result_window' => 100,
            ],
        ]);

        $this->artisan('elasticsearch:index-settings', ['index' => 'test_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_minimal_settings(): void
    {
        ElasticMocker::fake()->indexSettingsReturns('minimal_index', [
            'index' => [
                'number_of_shards' => '1',
                'number_of_replicas' => '0',
                'creation_date' => 1726564530000,
                'uuid' => 'minimal-uuid-123',
                'version' => ['created' => '8.10.4'],
            ],
        ]);

        $this->artisan('elasticsearch:index-settings', ['index' => 'minimal_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_empty_settings(): void
    {
        ElasticMocker::fake()->indexSettingsReturns('empty_settings_index', []);

        $this->artisan('elasticsearch:index-settings', ['index' => 'empty_settings_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-settings', ['index' => 'nonexistent_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:index-settings', ['index' => 'error_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_production_index_with_all_settings(): void
    {
        ElasticMocker::fake()->indexSettingsReturns('statements_production_2024.09.17', [
            'index' => [
                'number_of_shards' => '10',
                'number_of_replicas' => '2',
                'creation_date' => 1726560000000,
                'uuid' => 'prod-uuid-abc123-def456',
                'version' => ['created' => '8.10.4'],
                'refresh_interval' => '30s',
                'analysis' => [
                    'analyzer' => array_fill(0, 8, []),
                    'tokenizer' => array_fill(0, 4, []),
                    'filter' => array_fill(0, 12, []),
                ],
                'routing' => [
                    'allocation' => [
                        'include' => [
                            '_tier_preference' => 'data_hot,data_warm',
                        ],
                    ],
                ],
                'max_result_window' => 50000,
                'max_inner_result_window' => 500,
                'max_rescore_window' => 1000,
            ],
        ]);

        $this->artisan('elasticsearch:index-settings', ['index' => 'statements_production_2024.09.17'])
            ->assertExitCode(0);
    }

    public function test_command_handles_development_index(): void
    {
        ElasticMocker::fake()->indexSettingsReturns('dev_test_index', [
            'index' => [
                'number_of_shards' => '1',
                'number_of_replicas' => '0',
                'creation_date' => 1726577115000,
                'uuid' => 'dev-uuid-789',
                'version' => ['created' => '8.10.4'],
                'refresh_interval' => '1s',
            ],
        ]);

        $this->artisan('elasticsearch:index-settings', ['index' => 'dev_test_index'])
            ->assertExitCode(0);
    }
}
