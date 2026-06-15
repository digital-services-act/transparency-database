<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexAliasDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_deletes_alias(): void
    {
        ElasticMocker::fake()->deleteAliasSucceeds();

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'test_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_deletion(): void
    {
        ElasticMocker::fake()->deleteAliasSucceeds(false);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'test_index',
            'alias' => 'problematic_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'nonexistent_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_alias(): void
    {
        ElasticMocker::fake()
            ->exists()
            ->exists(false);

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'existing_index',
            'alias' => 'nonexistent_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_runtime_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'error_index',
            'alias' => 'error_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_complex_index_and_alias_names(): void
    {
        ElasticMocker::fake()->deleteAliasSucceeds();

        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'statements_production_2024.09.17',
            'alias' => 'old-production-index',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_cleanup_multiple_aliases(): void
    {
        ElasticMocker::fake()
            ->deleteAliasSucceeds()
            ->deleteAliasSucceeds();

        // Delete first alias
        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'shared_index',
            'alias' => 'old_alias_one',
        ])
            ->assertExitCode(0);

        // Delete second alias
        $this->artisan('elasticsearch:index-alias-delete', [
            'index' => 'shared_index',
            'alias' => 'old_alias_two',
        ])
            ->assertExitCode(0);
    }
}
