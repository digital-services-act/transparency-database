<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_deletes_existing_index(): void
    {
        ElasticMocker::fake()->deleteIndexSucceeds();

        $this->artisan('elasticsearch:index-delete', ['index' => 'test_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_deletion(): void
    {
        ElasticMocker::fake()->deleteIndexSucceeds(false);

        $this->artisan('elasticsearch:index-delete', ['index' => 'problematic_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-delete', ['index' => 'nonexistent_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_runtime_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:index-delete', ['index' => 'error_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_large_index_deletion(): void
    {
        ElasticMocker::fake()->deleteIndexSucceeds();

        $this->artisan('elasticsearch:index-delete', ['index' => 'large_production_index'])
            ->assertExitCode(0);
    }

    public function test_command_handles_index_with_special_characters(): void
    {
        ElasticMocker::fake()->deleteIndexSucceeds();

        $this->artisan('elasticsearch:index-delete', ['index' => 'test-index_2024.01'])
            ->assertExitCode(0);
    }
}
