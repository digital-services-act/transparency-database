<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\StatementElasticSearchableChunk;
use App\Jobs\StatementElasticSearchableChunkReverse;
use App\Jobs\StatementElasticSearchableRawChunk;
use App\Jobs\StatementElasticSearchableRawChunkReverse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ElasticIndexingStatsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_runs_once_with_an_empty_queue(): void
    {
        $this->artisan('elastic:indexing-stats', ['--once' => true])
            ->expectsOutputToContain('ElasticSearch Indexing Stats')
            ->expectsOutput('No statement Elasticsearch indexing jobs found in queue')
            ->assertSuccessful();
    }

    #[Test]
    public function it_reports_statement_elasticsearch_chunk_jobs_without_database_json_functions(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => StatementElasticSearchableChunk::class,
                'data' => [
                    'command' => serialize(new StatementElasticSearchableChunk(803, 900, 10)),
                ],
            ]),
            'attempts' => 1,
            'reserved_at' => null,
            'available_at' => now()->unix(),
            'created_at' => now()->unix(),
        ]);

        $this->assertSame(0, Artisan::call('elastic:indexing-stats', ['--once' => true]));

        $output = Artisan::output();

        $this->assertStringContainsString('Current Position (min)', $output);
        $this->assertStringContainsString('803', $output);
        $this->assertStringContainsString('Target Maximum', $output);
        $this->assertStringContainsString('900', $output);
        $this->assertStringContainsString('Active Jobs', $output);

        $this->assertFalse(
            collect($queries)->contains(fn (string $sql): bool => str_contains(strtolower($sql), 'json_extract')),
            'The command should not use database-specific JSON_EXTRACT queries.'
        );
    }

    #[Test]
    public function it_reports_all_statement_elasticsearch_chunk_job_variants(): void
    {
        $this->insertIndexingJob(new StatementElasticSearchableChunk(100, 500, 10));
        $this->insertIndexingJob(new StatementElasticSearchableChunkReverse(100, 450, 10));
        $this->insertIndexingJob(new StatementElasticSearchableRawChunk(100, 400, 10, false, true));
        $this->insertIndexingJob(new StatementElasticSearchableRawChunkReverse(100, 350, 10, false, true));

        $this->assertSame(0, Artisan::call('elastic:indexing-stats', ['--once' => true]));

        $output = Artisan::output();

        $this->assertStringContainsString('Job Type Summary', $output);
        $this->assertStringContainsString('Eloquent Forward', $output);
        $this->assertStringContainsString('Eloquent Reverse', $output);
        $this->assertStringContainsString('Raw Forward', $output);
        $this->assertStringContainsString('Raw Reverse', $output);
        $this->assertStringContainsString('Best Remaining', $output);
        $this->assertStringContainsString('Chain Summary', $output);
        $this->assertStringContainsString('Chain Rates: Need more data points', $output);
        $this->assertStringContainsString('Processing Rates: Need more data points', $output);
        $this->assertStringContainsString('Active Jobs Summary', $output);
    }

    #[Test]
    public function it_reports_independent_chains_for_multiple_raw_forward_roots(): void
    {
        $this->insertIndexingJob(new StatementElasticSearchableRawChunk(100, 200, 10, false, true));
        $this->insertIndexingJob(new StatementElasticSearchableRawChunk(300, 400, 10, false, true));
        $this->insertIndexingJob(new StatementElasticSearchableRawChunk(150, 200, 10, false, true));

        $this->assertSame(0, Artisan::call('elastic:indexing-stats', ['--once' => true]));

        $output = Artisan::output();

        $this->assertStringContainsString('Chain Summary', $output);
        $this->assertStringContainsString('Boundary', $output);
        $this->assertStringContainsString('Queued Remaining', $output);
        $this->assertStringContainsString('200', $output);
        $this->assertStringContainsString('400', $output);
    }

    #[Test]
    public function it_handles_jobs_serialized_before_the_benchmark_property_existed(): void
    {
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => StatementElasticSearchableChunk::class,
                'data' => [
                    'command' => 'O:40:"App\\Jobs\\StatementElasticSearchableChunk":4:{s:3:"min";i:803;s:3:"max";i:900;s:5:"chunk";i:10;s:5:"range";b:0;}',
                ],
            ]),
            'attempts' => 1,
            'reserved_at' => null,
            'available_at' => now()->unix(),
            'created_at' => now()->unix(),
        ]);

        $this->assertSame(0, Artisan::call('elastic:indexing-stats', ['--once' => true]));

        $output = Artisan::output();

        $this->assertStringContainsString('Eloquent Forward', $output);
        $this->assertStringContainsString('whereBetween', $output);
        $this->assertStringContainsString('Benchmark Enabled', $output);
        $this->assertStringContainsString('no', $output);
    }

    private function insertIndexingJob(object $job): void
    {
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => $job::class,
                'data' => [
                    'command' => serialize($job),
                ],
            ]),
            'attempts' => 1,
            'reserved_at' => null,
            'available_at' => now()->unix(),
            'created_at' => now()->unix(),
        ]);
    }
}
