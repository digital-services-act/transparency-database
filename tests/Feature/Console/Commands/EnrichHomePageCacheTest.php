<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class EnrichHomePageCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_with_all_option(): void
    {
        $elastic = ElasticMocker::fake()
            ->sqlCountReturns(12345)
            ->sqlCountReturns(8271);

        foreach (Statement::STATEMENT_CATEGORIES as $category) {
            $elastic->sqlCountReturns($category === Statement::STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH ? 50 : 10);
        }

        foreach (Statement::DECISION_VISIBILITIES as $decisionVisibility) {
            $elastic->sqlCountReturns($decisionVisibility === Statement::DECISION_VISIBILITY_CONTENT_REMOVED ? 40 : 10);
        }

        // Create test platforms to avoid mocking the model
        Platform::factory()->count(5)->create();

        // Mock Cache facade - using 90000 which is 25 * 60 * 60
        Cache::shouldReceive('remember')->with('grand_total', 90000, Mockery::type('Closure'))->andReturn(12345);
        Cache::shouldReceive('put')->with('grand_total', 12345, 90000);
        Cache::shouldReceive('put')->with('platforms_total', Mockery::type('int'), 90000);
        Cache::shouldReceive('put')->with('automated_decisions_percentage', 67, 90000);
        Cache::shouldReceive('put')->with('top_categories', Mockery::type('array'), 90000);
        Cache::shouldReceive('put')->with('top_decisions_visibility', Mockery::type('array'), 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--all' => true])
            ->assertExitCode(0);
    }

    public function test_it_runs_with_grandtotal_option(): void
    {
        ElasticMocker::fake()->sqlCountReturns(54321);

        // Mock Cache facade
        Cache::shouldReceive('put')->with('grand_total', 54321, 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--grandtotal' => true])
            ->assertExitCode(0);
    }

    public function test_it_runs_with_platformstotal_option(): void
    {
        // Create test platforms to count
        Platform::factory()->count(3)->create();

        // Mock Cache facade
        Cache::shouldReceive('put')->with('platforms_total', Mockery::type('int'), 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--platformstotal' => true])
            ->assertExitCode(0);
    }

    public function test_it_runs_with_automateddecisionspercentage_option(): void
    {
        ElasticMocker::fake()->sqlCountReturns(85);

        // Mock Cache facade
        Cache::shouldReceive('remember')->with('grand_total', 90000, Mockery::type('Closure'))->andReturn(100);
        Cache::shouldReceive('put')->with('automated_decisions_percentage', 85, 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--automateddecisionspercentage' => true])
            ->assertExitCode(0);
    }
}
