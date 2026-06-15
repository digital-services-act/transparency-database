<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexAliasSwapTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_swaps_alias(): void
    {
        ElasticMocker::fake()->swapAliasSucceeds();

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'old_index',
            'target' => 'new_index',
            'alias' => 'current',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_unacknowledged_swap(): void
    {
        ElasticMocker::fake()->swapAliasSucceeds(false);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'source_index',
            'target' => 'target_index',
            'alias' => 'production',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_source_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'nonexistent_index',
            'target' => 'target_index',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_target_index(): void
    {
        ElasticMocker::fake()
            ->exists()
            ->exists(false);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'source_index',
            'target' => 'nonexistent_target',
            'alias' => 'test_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_alias_not_on_source(): void
    {
        ElasticMocker::fake()
            ->exists()
            ->exists()
            ->exists(false);

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'source_index',
            'target' => 'target_index',
            'alias' => 'missing_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_alias_already_on_target(): void
    {
        ElasticMocker::fake()
            ->exists()
            ->exists()
            ->exists()
            ->exists();

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'source_index',
            'target' => 'target_index',
            'alias' => 'existing_alias',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:index-alias-swap', [
            'index' => 'error_index',
            'target' => 'target_index',
            'alias' => 'error_alias',
        ])
            ->assertExitCode(0);
    }
}
