<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchRemoveSorTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_successfully_removes_document(): void
    {
        ElasticMocker::fake()->removeDocumentSucceeds(version: 2);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '12345',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_document_removal_without_version(): void
    {
        ElasticMocker::fake()->removeDocumentSucceeds();

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '67890',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_non_existent_index(): void
    {
        ElasticMocker::fake()->exists(false);

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'nonexistent_index',
            'id' => '12345',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_invalid_document_id(): void
    {
        ElasticMocker::fake()->exists();

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '0',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_document_not_found(): void
    {
        ElasticMocker::fake()
            ->exists()
            ->exception(new RuntimeException('not_found'));

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '99999',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_general_exception(): void
    {
        ElasticMocker::fake()->exception(new RuntimeException('Connection timeout'));

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'error_index',
            'id' => '12345',
        ])
            ->assertExitCode(0);
    }

    public function test_command_handles_negative_document_id(): void
    {
        ElasticMocker::fake()->exists();

        $this->artisan('elasticsearch:index-removestatement', [
            'index' => 'test_index',
            'id' => '-1',
        ])
            ->assertExitCode(0);
    }
}
