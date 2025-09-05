<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Platform;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class EnrichHomePageCacheTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_runs_with_all_option(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('grandTotalNoCache')->once()->andReturn(12345);
        $serviceMock->shouldReceive('fullyAutomatedDecisionPercentageNoCache')->once()->andReturn(67);
        $serviceMock->shouldReceive('topCategoriesNoCache')->once()->andReturn(['category1', 'category2']);
        $serviceMock->shouldReceive('topDecisionVisibilitiesNoCache')->once()->andReturn(['visibility1', 'visibility2']);

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Create test platforms to avoid mocking the model
        Platform::factory()->count(5)->create();

        // Mock Cache facade - using 90000 which is 25 * 60 * 60
        Cache::shouldReceive('get')->with('reindexing', false)->andReturn(false);
        Cache::shouldReceive('put')->with('grand_total', 12345, 90000);
        Cache::shouldReceive('put')->with('platforms_total', Mockery::type('int'), 90000);
        Cache::shouldReceive('put')->with('automated_decisions_percentage', 67, 90000);
        Cache::shouldReceive('put')->with('top_categories', ['category1', 'category2'], 90000);
        Cache::shouldReceive('put')->with('top_decisions_visibility', ['visibility1', 'visibility2'], 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--all' => true])
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_runs_with_grandtotal_option(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('grandTotalNoCache')->once()->andReturn(54321);

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Mock Cache facade
        Cache::shouldReceive('get')->with('reindexing', false)->andReturn(false);
        Cache::shouldReceive('put')->with('grand_total', 54321, 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--grandtotal' => true])
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_handles_reindexing_scenario_for_grandtotal(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('totalForDate')->with(Mockery::type(Carbon::class))->once()->andReturn(100);

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Mock Cache facade for reindexing scenario
        Cache::shouldReceive('get')->with('reindexing', false)->andReturn(true);
        Cache::shouldReceive('get')->with('grand_total')->andReturn(1000);
        Cache::shouldReceive('put')->with('grand_total', 1100, 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--grandtotal' => true])
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_runs_with_platformstotal_option(): void
    {
        // Create test platforms to count
        Platform::factory()->count(3)->create();

        // Mock Cache facade
        Cache::shouldReceive('put')->with('platforms_total', Mockery::type('int'), 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--platformstotal' => true])
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function it_runs_with_automateddecisionspercentage_option(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('fullyAutomatedDecisionPercentageNoCache')->once()->andReturn(85);

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Mock Cache facade
        Cache::shouldReceive('put')->with('automated_decisions_percentage', 85, 90000);

        // Run the command
        $this->artisan('enrich-home-page-cache', ['--automateddecisionspercentage' => true])
            ->assertExitCode(0);
    }
}