<?php

namespace Tests\Feature\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AggregatesFreezeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_runs_with_successful_aggregates(): void
    {
        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('getAllowedAggregateAttributes')
            ->twice() // Once at start, once for headers
            ->andReturn(['category']);

        $mockResults = [
            'aggregates' => [
                [
                    'category' => 'test_category',
                    'platform_name' => 'Test Platform',
                    'total' => 100,
                ],
            ],
        ];

        $serviceMock->shouldReceive('processDateAggregate')
            ->once()
            ->andReturn($mockResults);

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Mock Storage facades
        $s3DiskMock = Mockery::mock();
        $s3DiskMock->shouldReceive('put')->twice()->andReturn(true); // CSV and JSON

        Storage::shouldReceive('disk')->with('s3ds')->andReturn($s3DiskMock);

        $tempDir = sys_get_temp_dir().'/test_aggregates_'.time().'/';
        mkdir($tempDir, 0755, true);
        Storage::shouldReceive('path')->with('')->andReturn($tempDir);

        // Mock Log facade
        Log::shouldReceive('info')->once()->with(Mockery::pattern('/Number of aggregates.*1/'));

        // Run the command
        $this->artisan('aggregates-freeze', ['date' => '30']);

        // Clean up
        if (is_dir($tempDir)) {
            array_map('unlink', glob("$tempDir/*"));
            rmdir($tempDir);
        }

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_logs_error_and_returns_early_when_no_aggregates(): void
    {
        // Mock Storage first, before any other setup
        $s3DiskMock = Mockery::mock();
        Storage::shouldReceive('disk')->with('s3ds')->andReturn($s3DiskMock);
        Storage::shouldReceive('path')->with('')->andReturn('/tmp/');

        // Mock the StatementElasticSearchService
        $serviceMock = Mockery::mock(StatementElasticSearchService::class);
        $serviceMock->shouldReceive('getAllowedAggregateAttributes')
            ->once() // Only called once, returns before headers call
            ->andReturn(['category']);

        // Return empty results
        $emptyResults = ['aggregates' => []];
        $serviceMock->shouldReceive('processDateAggregate')
            ->once()
            ->andReturn($emptyResults);

        $this->app->instance(StatementElasticSearchService::class, $serviceMock);

        // Mock Log facade - expect info and error messages
        Log::shouldReceive('info')->once()->with(Mockery::pattern('/Number of aggregates.*0/'));
        Log::shouldReceive('error')->once()->with(Mockery::pattern('/The number of aggregates.*is 0/'));

        // Run the command - should return early without file operations
        $this->artisan('aggregates-freeze', ['date' => '30']);

        $this->assertTrue(true);
    }
}
