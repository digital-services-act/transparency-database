<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\ElasticSearchIndexList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class ElasticSearchIndexListTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_instantiated(): void
    {
        $command = new ElasticSearchIndexList;

        $this->assertInstanceOf(ElasticSearchIndexList::class, $command);
        $this->assertEquals('elasticsearch:index-list', $command->getName());
        $this->assertEquals('Get some info about the elasticsearch.', $command->getDescription());
    }

    public function test_it_lists_elasticsearch_indices_with_elastic_mocker(): void
    {
        ElasticMocker::fake()
            ->indexListReturns([
                'statements-2025-09-16',
                'statements-2025-09-15',
                'test-index',
            ]);

        $this->artisan('elasticsearch:index-list')
            ->expectsTable(['Indexes'], [
                ['statements-2025-09-16'],
                ['statements-2025-09-15'],
                ['test-index'],
            ])
            ->assertExitCode(0);
    }

    public function test_it_handles_empty_index_list_gracefully(): void
    {
        ElasticMocker::fake()->indexListReturns([]);

        // Should show empty table but not crash
        $this->artisan('elasticsearch:index-list')
            ->expectsTable(['Indexes'], [])
            ->assertExitCode(0);
    }

    public function test_it_handles_single_index(): void
    {
        ElasticMocker::fake()->indexListReturns(['single-index']);

        $this->artisan('elasticsearch:index-list')
            ->expectsTable(['Indexes'], [
                ['single-index'],
            ])
            ->assertExitCode(0);
    }
}
