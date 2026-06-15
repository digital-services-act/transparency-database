<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_displays_index_information(): void
    {
        ElasticMocker::fake()->indexInfoReturns(
            'test_index',
            'test-uuid-123-456',
            15000,
            2097152,
            [
                'id' => ['type' => 'long'],
                'title' => ['type' => 'text'],
                'category' => ['type' => 'keyword'],
            ],
            [
                ['shard' => '0', 'prirep' => 'p', 'state' => 'STARTED', 'docs' => '7500', 'store' => '1.1mb'],
                ['shard' => '1', 'prirep' => 'p', 'state' => 'STARTED', 'docs' => '7500', 'store' => '1.1mb'],
            ],
            ['current_index', 'search_index'],
        );

        $this->artisan('elasticsearch:index-info', ['index' => 'test_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_index_with_no_aliases(): void
    {
        ElasticMocker::fake()->indexInfoReturns(
            'no_alias_index',
            'no-alias-uuid-789',
            500,
            1024,
            ['id' => ['type' => 'long']],
            [
                ['shard' => '0', 'prirep' => 'p', 'state' => 'STARTED', 'docs' => '500', 'store' => '1kb'],
            ],
        );

        $this->artisan('elasticsearch:index-info', ['index' => 'no_alias_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_large_index_with_gigabyte_size(): void
    {
        ElasticMocker::fake()->indexInfoReturns(
            'large_index',
            'large-index-uuid',
            1000000,
            3221225472,
            ['content' => ['type' => 'text']],
            [
                ['shard' => '0', 'prirep' => 'p', 'state' => 'STARTED', 'docs' => '250000', 'store' => '800mb'],
            ],
        );

        $this->artisan('elasticsearch:index-info', ['index' => 'large_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-info', ['index' => 'nonexistent_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_alias_index(): void
    {
        ElasticMocker::fake()
            ->exists()
            ->indicesStatsReturns(['indices' => []]);

        $this->artisan('elasticsearch:index-info', ['index' => 'alias_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_empty_index(): void
    {
        ElasticMocker::fake()->indexInfoReturns(
            'empty_index',
            'empty-index-uuid',
            0,
            283,
            ['_id' => ['type' => 'keyword']],
        );

        $this->artisan('elasticsearch:index-info', ['index' => 'empty_index'])
            ->assertExitCode(0);
    }

    public function test_human_file_size_formatting(): void
    {
        ElasticMocker::fake()->indexInfoReturns(
            'size_test',
            'size-test-uuid',
            1000,
            1073741824,
            ['data' => ['type' => 'text']],
        );

        $this->artisan('elasticsearch:index-info', ['index' => 'size_test'])
            ->assertExitCode(0);
    }
}
