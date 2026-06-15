<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexAliasCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_creates_alias(): void
    {
        ElasticMocker::fake()->createAliasSucceeds();

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'test_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_creation(): void
    {
        ElasticMocker::fake()->createAliasSucceeds(false);

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'test_index',
            'alias' => 'problematic_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'nonexistent_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_existing_alias(): void
    {
        ElasticMocker::fake()
            ->exists()
            ->exists();

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'existing_index',
            'alias' => 'existing_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_runtime_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'error_index',
            'alias' => 'error_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_complex_index_and_alias_names(): void
    {
        ElasticMocker::fake()->createAliasSucceeds();

        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'statements_production_2024.09.17',
            'alias' => 'current-production-index',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_multiple_alias_operations(): void
    {
        ElasticMocker::fake()
            ->createAliasSucceeds()
            ->createAliasSucceeds();

        // First alias
        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'shared_index',
            'alias' => 'alias_one',
        ])
            ->assertExitCode(0);

        // Second alias
        $this->artisan('elasticsearch:index-alias-create', [
            'index' => 'shared_index',
            'alias' => 'alias_two',
        ])
            ->assertExitCode(0);
    }
}
