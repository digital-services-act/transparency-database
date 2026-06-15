<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Platform;
use App\Services\DayArchiveWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Support\ElasticMocker;
use Tests\TestCase;

class AggregatesFreezeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_with_successful_aggregates(): void
    {
        $platform = Platform::factory()->create(['name' => 'Test Platform']);
        ElasticMocker::fake()->aggregateBucketsReturn([
            $this->aggregateBucket($platform),
        ]);

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

        ElasticMocker::fake()->aggregateBucketsReturn([]);

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

        $platform = Platform::factory()->create(['name' => 'Test Platform']);
        ElasticMocker::fake()->aggregateBucketsReturn([
            $this->aggregateBucket($platform, Carbon::parse('2026-06-02 00:00:00', 'UTC')),
        ]);

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
            ->with('aggregates-2026-06-02.json', Mockery::on(static function (string $json): bool {
                $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

                return ($payload['date'] ?? null) === '2026-06-02'
                    && ($payload['aggregates'][0]['category'] ?? null) === 'test_category'
                    && ($payload['aggregates'][0]['platform_name'] ?? null) === 'Test Platform'
                    && ($payload['aggregates'][0]['total'] ?? null) === 100;
            }))
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

    private function aggregateBucket(Platform $platform, ?Carbon $receivedDate = null): array
    {
        $receivedDate ??= Carbon::parse('2026-06-01 00:00:00', 'UTC');

        return [
            'key' => [
                'automated_decision' => 'AUTOMATED_DECISION_FULLY',
                'automated_detection' => 1,
                'category' => 'test_category',
                'content_type_single' => 'CONTENT_TYPE_TEXT',
                'decision_account' => 'DECISION_ACCOUNT_SUSPENDED',
                'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
                'decision_monetary' => 'DECISION_MONETARY_SUSPENSION',
                'decision_provision' => 'DECISION_PROVISION_TOTAL_SUSPENSION',
                'decision_visibility_single' => 'DECISION_VISIBILITY_CONTENT_REMOVED',
                'platform_id' => $platform->id,
                'received_date' => $receivedDate->getTimestampMs(),
                'source_type' => 'SOURCE_ARTICLE_16',
            ],
            'doc_count' => 100,
        ];
    }
}
