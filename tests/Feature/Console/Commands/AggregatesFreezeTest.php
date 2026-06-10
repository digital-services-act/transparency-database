<?php

namespace Tests\Feature\Console\Commands;

use App\Services\DayArchiveWorkspace;
use App\Services\StatementElasticAggregationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class AggregatesFreezeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_with_successful_aggregates(): void
    {
        // Mock the StatementElasticAggregationService
        $serviceMock = Mockery::mock(StatementElasticAggregationService::class);
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

        $this->app->instance(StatementElasticAggregationService::class, $serviceMock);

        // Mock Storage facades
        $s3DiskMock = Mockery::mock();
        $s3DiskMock->shouldReceive('put')->twice()->andReturn(true); // CSV and JSON

        Storage::shouldReceive('disk')->with('s3ds')->andReturn($s3DiskMock);

        $tempDir = sys_get_temp_dir().'/test_aggregates_'.uniqid().'/';
        File::makeDirectory($tempDir);
        $this->app->instance(DayArchiveWorkspace::class, new DayArchiveWorkspace($tempDir));

        // Mock Log facade
        Log::shouldReceive('info')->once()->with(Mockery::pattern('/Number of aggregates.*1/'));

        try {
            // Run the command
            $this->artisan('aggregates-freeze', ['date' => '30']);
        } finally {
            // Clean up
            File::deleteDirectory($tempDir);
        }

        $this->assertTrue(true);
    }

    public function test_it_logs_error_and_returns_early_when_no_aggregates(): void
    {
        // Mock Storage first, before any other setup
        Storage::shouldReceive('disk')->never();

        // Mock the StatementElasticAggregationService
        $serviceMock = Mockery::mock(StatementElasticAggregationService::class);
        $serviceMock->shouldReceive('getAllowedAggregateAttributes')
            ->once() // Only called once, returns before headers call
            ->andReturn(['category']);

        // Return empty results
        $emptyResults = ['aggregates' => []];
        $serviceMock->shouldReceive('processDateAggregate')
            ->once()
            ->andReturn($emptyResults);

        $this->app->instance(StatementElasticAggregationService::class, $serviceMock);

        // Mock Log facade - expect info and error messages
        Log::shouldReceive('info')->once()->with(Mockery::pattern('/Number of aggregates.*0/'));
        Log::shouldReceive('error')->once()->with(Mockery::pattern('/The number of aggregates.*is 0/'));

        // Run the command - should return early without file operations
        $this->artisan('aggregates-freeze', ['date' => '30']);

        $this->assertTrue(true);
    }

    public function test_it_uses_yesterday_argument_for_aggregate_date_and_output_files(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-03 12:00:00', 'UTC'));

        $tempDir = sys_get_temp_dir().'/test_aggregates_yesterday_'.uniqid().'/';
        File::makeDirectory($tempDir);
        $this->app->instance(DayArchiveWorkspace::class, new DayArchiveWorkspace($tempDir));

        $mockResults = [
            'aggregates' => [
                [
                    'category' => 'test_category',
                    'platform_name' => 'Test Platform',
                    'total' => 100,
                ],
            ],
        ];

        $serviceMock = Mockery::mock(StatementElasticAggregationService::class);
        $serviceMock->shouldReceive('getAllowedAggregateAttributes')
            ->twice()
            ->andReturn(['category']);
        $serviceMock->shouldReceive('processDateAggregate')
            ->once()
            ->with(
                Mockery::on(fn (Carbon $date): bool => $date->equalTo(Carbon::parse('2026-06-02 00:00:00', 'UTC'))),
                ['category'],
                false
            )
            ->andReturn($mockResults);

        $this->app->instance(StatementElasticAggregationService::class, $serviceMock);

        $expectedCsvPath = $tempDir.'aggregates-2026-06-02.csv';

        $s3DiskMock = Mockery::mock();
        $s3DiskMock->shouldReceive('put')
            ->once()
            ->with('aggregates-2026-06-02.csv', Mockery::on(
                static function (mixed $resource) use ($expectedCsvPath): bool {
                    if (! is_resource($resource)) {
                        return false;
                    }

                    $metadata = stream_get_meta_data($resource);

                    return ($metadata['uri'] ?? null) === $expectedCsvPath;
                }
            ))
            ->andReturn(true);
        $s3DiskMock->shouldReceive('put')
            ->once()
            ->with('aggregates-2026-06-02.json', json_encode($mockResults, JSON_THROW_ON_ERROR))
            ->andReturn(true);

        Storage::shouldReceive('disk')->with('s3ds')->andReturn($s3DiskMock);

        Log::shouldReceive('info')->once()->with(Mockery::pattern('/Number of aggregates.*1/'));

        try {
            $this->artisan('aggregates-freeze', ['date' => 'yesterday'])->assertSuccessful();

            $this->assertFileDoesNotExist($expectedCsvPath);
        } finally {
            Carbon::setTestNow();

            File::deleteDirectory($tempDir);
        }
    }
}
