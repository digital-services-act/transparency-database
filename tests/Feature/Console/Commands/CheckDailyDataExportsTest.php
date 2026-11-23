<?php

namespace Tests\Feature\Console\Commands;

use App\Models\DayArchive;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckDailyDataExportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-20 12:00:00', 'UTC'));
    }

    public function test_it_counts_aggregates_and_posts_csv_metrics_to_teams_in_production(): void
    {
        Statement::factory()->create([
            'created_at' => '2024-03-02 08:00:00',
            'updated_at' => '2024-03-02 08:00:00',
        ]);
        Statement::factory()->create([
            'created_at' => '2024-04-02 08:00:00',
            'updated_at' => '2024-04-02 08:00:00',
        ]);
        DB::table('platform_puids')->insert([
            [
                'platform_id' => 1,
                'puid' => 'first-puid',
                'created_at' => '2023-03-02 08:00:00',
                'updated_at' => '2023-03-02 08:00:00',
            ],
            [
                'platform_id' => 1,
                'puid' => 'second-puid',
                'created_at' => '2023-04-02 08:00:00',
                'updated_at' => '2023-04-02 08:00:00',
            ],
        ]);
        DB::table('failed_jobs')->insert([
            [
                'uuid' => '00000000-0000-4000-8000-000000000001',
                'connection' => 'database',
                'queue' => 'default',
                'payload' => '{}',
                'exception' => 'First failure',
                'failed_at' => '2026-11-20 08:00:00',
            ],
            [
                'uuid' => '00000000-0000-4000-8000-000000000002',
                'connection' => 'database',
                'queue' => 'default',
                'payload' => '{}',
                'exception' => 'Second failure',
                'failed_at' => '2026-11-20 09:00:00',
            ],
        ]);

        config([
            'app.env' => 'production',
            'services.teams.daily_webhook' => 'https://teams.example.test/webhook',
        ]);
        DayArchive::factory()->completed()->global()->create([
            'date' => '2026-11-19',
            'completed_at' => '2026-11-20 08:00:00',
            'total' => 30_006_787,
            'size' => 23 * (1024 ** 3),
        ]);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response(['aggregates' => [[], [], []]], 200),
            'teams.example.test/*' => Http::response('', 200),
        ]);

        $report = implode("\n", [
            '2026/11/19',
            'Aggregates -> Good 3 Combos',
            'CSVs -> Good, SORs total: 30 006 787 full size: 23 GB',
            'CSVs completed_at -> 2026-11-20 08:00:00',
            'failed_jobs -> 2 rows',
            'statements_beta min(created_at) -> 2024-03-02 08:00:00',
            'platform_puids min(created_at) -> 2023-03-02 08:00:00',
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('2026/11/19')
            ->expectsOutput('Aggregates -> Good 3 Combos')
            ->expectsOutput('CSVs -> Good, SORs total: 30 006 787 full size: 23 GB')
            ->expectsOutput('CSVs completed_at -> 2026-11-20 08:00:00')
            ->expectsOutput('failed_jobs -> 2 rows')
            ->expectsOutput('statements_beta min(created_at) -> 2024-03-02 08:00:00')
            ->expectsOutput('platform_puids min(created_at) -> 2023-03-02 08:00:00')
            ->expectsOutput('Teams card sent.')
            ->assertSuccessful();

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://transparency.dsa.ec.europa.eu/explore-data/download/aggregates-2026-11-19.json'
            && $request->method() === 'GET'
        );
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://teams.example.test/webhook'
            && $request->data()['attachments'][0]['content']['body'][0]['text'] === $report
        );
    }

    public function test_it_only_prints_the_report_outside_production(): void
    {
        config([
            'app.env' => 'testing',
            'services.teams.daily_webhook' => 'https://teams.example.test/webhook',
        ]);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response(['aggregates' => [[]]], 200),
            'teams.example.test/*' => Http::response('', 200),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('2026/11/19')
            ->expectsOutput('Aggregates -> Good 1 Combos')
            ->expectsOutput('CSVs -> Not present!')
            ->assertFailed();

        Http::assertSentCount(1);
        Http::assertNotSent(fn (Request $request): bool => $request->url() === 'https://teams.example.test/webhook');
    }

    public function test_it_follows_the_production_redirect_to_the_signed_cellar_json(): void
    {
        config(['app.env' => 'testing']);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response('', 302, [
                'Location' => 'https://tdb-prd-s3.cellar-c2.services.clever-cloud.com/aggregates-2026-11-19.json?X-Amz-Signature=test',
            ]),
            'tdb-prd-s3.cellar-c2.services.clever-cloud.com/*' => Http::response(['aggregates' => [[], []]], 200),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('2026/11/19')
            ->expectsOutput('Aggregates -> Good 2 Combos')
            ->expectsOutput('CSVs -> Not present!')
            ->assertFailed();

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://transparency.dsa.ec.europa.eu/explore-data/download/aggregates-2026-11-19.json');
        Http::assertSent(fn (Request $request): bool => parse_url($request->url(), PHP_URL_HOST) === 'tdb-prd-s3.cellar-c2.services.clever-cloud.com');
    }

    public function test_it_reports_an_http_error_and_a_missing_archive(): void
    {
        config(['app.env' => 'testing']);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response('', 404),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('2026/11/19')
            ->expectsOutput('Aggregates -> 404 Not Found')
            ->expectsOutput('CSVs -> Not present!')
            ->assertFailed();

        Http::assertSentCount(1);
    }

    public function test_it_reports_a_connection_failure_for_the_aggregate_download(): void
    {
        config(['app.env' => 'testing']);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::failedConnection(),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('2026/11/19')
            ->expectsOutput('Aggregates -> Connection failed')
            ->expectsOutput('CSVs -> Not present!')
            ->assertFailed();
    }

    public function test_it_reports_invalid_aggregate_json(): void
    {
        config(['app.env' => 'testing']);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response('not json', 200),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('Aggregates -> Invalid JSON')
            ->expectsOutput('CSVs -> Not present!')
            ->assertFailed();
    }

    public function test_it_reports_an_unexpected_aggregate_json_shape(): void
    {
        config(['app.env' => 'testing']);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response(['results' => []], 200),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('Aggregates -> Unexpected JSON format')
            ->expectsOutput('CSVs -> Not present!')
            ->assertFailed();
    }

    public function test_it_reports_a_missing_webhook_when_run_in_production(): void
    {
        config([
            'app.env' => 'production',
            'services.teams.daily_webhook' => null,
        ]);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response(['aggregates' => []], 200),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('2026/11/19')
            ->expectsOutput('Aggregates -> Good 0 Combos')
            ->expectsOutput('CSVs -> Not present!')
            ->expectsOutput('DAILY_TEAMS_WEBHOOK is not configured.')
            ->assertFailed();
    }

    public function test_it_reports_a_webhook_connection_failure_in_production(): void
    {
        config([
            'app.env' => 'production',
            'services.teams.daily_webhook' => 'https://teams.example.test/webhook',
        ]);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response(['aggregates' => []], 200),
            'teams.example.test/*' => Http::failedConnection(),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('Could not connect to the daily Teams webhook.')
            ->assertFailed();
    }

    public function test_it_reports_a_webhook_http_error_in_production(): void
    {
        config([
            'app.env' => 'production',
            'services.teams.daily_webhook' => 'https://teams.example.test/webhook',
        ]);
        Http::fake([
            'transparency.dsa.ec.europa.eu/*' => Http::response(['aggregates' => []], 200),
            'teams.example.test/*' => Http::response('', 500),
        ]);

        $this->artisan('exports:check-yesterday')
            ->expectsOutput('The daily Teams webhook returned HTTP 500.')
            ->assertFailed();
    }
}
