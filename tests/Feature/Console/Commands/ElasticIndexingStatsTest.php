<?php

namespace Tests\Feature\Console\Commands;

use App\Jobs\StatementElasticSearchableChunk;
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
            ->expectsOutput('No StatementElasticSearchableChunk jobs found in queue')
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
}
