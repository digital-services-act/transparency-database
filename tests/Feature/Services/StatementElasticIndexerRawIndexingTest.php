<?php

namespace Tests\Feature\Services;

use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementElasticConnectionService;
use App\Services\StatementElasticIndexerService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use ReflectionClass;
use RuntimeException;
use stdClass;
use Tests\TestCase;
use Throwable;

class StatementElasticIndexerRawIndexingTest extends TestCase
{
    use RefreshDatabase;

    public function test_raw_statement_payload_matches_current_searchable_payload(): void
    {
        $statement = Statement::factory()->create();
        $service = app(StatementElasticIndexerService::class);

        $rawStatement = $service->rawStatementRowsForIdRange($statement->id, $statement->id)->first();

        $this->assertSame(
            $this->normalize($statement->toSearchableArray()),
            $this->normalize($service->rawStatementRowToSearchableArray($rawStatement)),
        );
    }

    public function test_raw_statement_payload_matches_current_payload_for_deleted_platform(): void
    {
        $statement = Statement::factory()->create();
        Platform::query()->findOrFail($statement->platform_id)->delete();
        Cache::flush();

        $service = app(StatementElasticIndexerService::class);
        $rawStatement = $service->rawStatementRowsForIdRange($statement->id, $statement->id)->first();

        $this->assertSame(
            $this->normalize($statement->toSearchableArray()),
            $this->normalize($service->rawStatementRowToSearchableArray($rawStatement)),
        );
    }

    public function test_index_statement_returns_elasticsearch_result_and_sends_alias_request(): void
    {
        [$service, $httpClient] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient($this->elasticResponse(['result' => 'created'])),
        );
        $statement = Statement::factory()->create();

        $this->assertSame('created', $service->indexStatement($statement));

        $request = $httpClient->requests[0];
        parse_str($request->getUri()->getQuery(), $query);

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/test_statement_index/_doc/'.$statement->id, $request->getUri()->getPath());
        $this->assertSame('true', $query['require_alias']);
    }

    public function test_index_statement_throws_when_elasticsearch_result_is_unexpected(): void
    {
        [$service] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient($this->elasticResponse(['result' => 'noop'])),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Elasticsearch index error');

        $service->indexStatement(Statement::factory()->create());
    }

    public function test_bulk_index_statements_sends_ndjson_for_eloquent_collection(): void
    {
        [$service, $httpClient] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient($this->elasticResponse()),
        );
        $statement = Statement::factory()->create(['id' => 701]);

        $service->bulkIndexStatements(new EloquentCollection([$statement]));

        $request = $httpClient->requests[0];
        parse_str($request->getUri()->getQuery(), $query);
        $payload = (string) $request->getBody();

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/_bulk', $request->getUri()->getPath());
        $this->assertSame('true', $query['require_alias']);
        $this->assertStringContainsString('"index":{"_index":"test_statement_index","_id":701}', $payload);
        $this->assertStringContainsString('"id":701', $payload);
    }

    public function test_bulk_index_statements_ignores_empty_collections(): void
    {
        [$service, $httpClient] = $this->indexerWithHttpClient(new FakeElasticHttpClient);

        $service->bulkIndexStatements(new EloquentCollection);

        $this->assertSame([], $httpClient->requests);
    }

    public function test_benchmark_bulk_index_statements_reports_metrics(): void
    {
        [$service] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient($this->elasticResponse()),
        );
        $statement = Statement::factory()->create(['id' => 702]);

        $metrics = $service->benchmarkBulkIndexStatements(new EloquentCollection([$statement]));

        $this->assertSame(1, $metrics['rows']);
        $this->assertSame(1, $metrics['elastic_attempts']);
        $this->assertSame(0, $metrics['elastic_retries']);
        $this->assertSame(0, $metrics['elastic_retry_sleep_ms']);
        $this->assertGreaterThan(0, $metrics['payload_bytes']);
        $this->assertArrayHasKey('transform_ms', $metrics);
        $this->assertArrayHasKey('ndjson_ms', $metrics);
        $this->assertArrayHasKey('elastic_ms', $metrics);
        $this->assertArrayHasKey('total_ms', $metrics);
    }

    public function test_benchmark_bulk_index_statements_skips_empty_payloads(): void
    {
        [$service] = $this->indexerWithHttpClient(new FakeElasticHttpClient);

        $metrics = $service->benchmarkBulkIndexStatements(new EloquentCollection);

        $this->assertSame(0, $metrics['rows']);
        $this->assertSame(0, $metrics['elastic_attempts']);
        $this->assertSame(0, $metrics['elastic_retries']);
        $this->assertSame(0, $metrics['payload_bytes']);
    }

    public function test_raw_statement_queries_support_ranges_dates_latest_and_direction_validation(): void
    {
        Statement::factory()->create([
            'id' => 900000000801,
            'created_at' => '2035-06-01 08:00:00',
        ]);
        Statement::factory()->create([
            'id' => 900000000802,
            'created_at' => '2035-06-01 09:00:00',
        ]);
        Statement::factory()->create([
            'id' => 900000000803,
            'created_at' => '2035-06-02 09:00:00',
        ]);

        $service = app(StatementElasticIndexerService::class);

        $this->assertSame([900000000803, 900000000802, 900000000801], $service->rawStatementRowsForIdRange(900000000801, 900000000803, false, 'desc')->pluck('id')->all());
        $this->assertSame([900000000801, 900000000802], $service->rawStatementRowsForDate(Carbon::parse('2035-06-01'), 10)->pluck('id')->all());
        $this->assertSame(900000000803, $service->latestRawStatementRows(1)->first()->id);
        $this->assertCount(3, $service->searchableArraysFromRawStatementRows($service->rawStatementRowsForIdRange(900000000801, 900000000803)));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Raw statement sort direction must be asc or desc.');

        $service->rawStatementRowsForIdRange(900000000801, 900000000803, true, 'sideways');
    }

    public function test_bulk_index_raw_statement_rows_and_id_ranges(): void
    {
        [$service, $httpClient] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient(
                $this->elasticResponse(),
                $this->elasticResponse(),
            ),
        );
        $statement = Statement::factory()->create(['id' => 901]);
        $rawRows = $service->rawStatementRowsForIdRange($statement->id, $statement->id);

        $service->bulkIndexRawStatementsForIdRange($statement->id, $statement->id);
        $metrics = $service->benchmarkBulkIndexRawStatementRows($rawRows);

        $this->assertCount(2, $httpClient->requests);
        $this->assertSame(1, $metrics['rows']);
        $this->assertSame(1, $metrics['elastic_attempts']);
    }

    public function test_benchmark_bulk_index_raw_statements_for_id_range_includes_fetch_time(): void
    {
        [$service] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient($this->elasticResponse()),
        );
        $statement = Statement::factory()->create(['id' => 902]);

        $metrics = $service->benchmarkBulkIndexRawStatementsForIdRange($statement->id, $statement->id);

        $this->assertSame(1, $metrics['rows']);
        $this->assertSame(1, $metrics['elastic_attempts']);
        $this->assertArrayHasKey('fetch_ms', $metrics);
        $this->assertGreaterThanOrEqual($metrics['fetch_ms'], $metrics['total_ms']);
    }

    public function test_bulk_index_raw_statement_rows_ignores_empty_collections(): void
    {
        [$service, $httpClient] = $this->indexerWithHttpClient(new FakeElasticHttpClient);

        $service->bulkIndexRawStatementRows(collect());
        $metrics = $service->benchmarkBulkIndexRawStatementRows(collect());

        $this->assertSame([], $httpClient->requests);
        $this->assertSame(0, $metrics['rows']);
        $this->assertSame(0, $metrics['elastic_attempts']);
    }

    public function test_build_bulk_body_returns_expected_ndjson(): void
    {
        [$service] = $this->indexerWithHttpClient(new FakeElasticHttpClient);

        $body = $service->buildBulkBodyFromSearchableArrays([
            ['id' => 1001, 'field' => 'value'],
        ]);

        $this->assertSame(
            '{"index":{"_index":"test_statement_index","_id":1001}}'."\n".
            '{"id":1001,"field":"value"}'."\n",
            $body,
        );
        $this->assertSame('', $service->buildBulkBodyFromSearchableArrays([]));
    }

    public function test_bulk_retries_no_node_exceptions_and_rebuilds_client(): void
    {
        config(['elasticsearch.bulk_retry_delays_ms' => [1]]);
        Log::spy();

        $firstHttpClient = new FakeElasticHttpClient(new NoNodeAvailableException('node unavailable'));
        $secondHttpClient = new FakeElasticHttpClient($this->elasticResponse());
        [$service, , $connection] = $this->indexerWithHttpClients($firstHttpClient, $secondHttpClient);
        $statement = Statement::factory()->create(['id' => 1101]);

        $metrics = $service->benchmarkBulkIndexStatements(new EloquentCollection([$statement]));

        $this->assertSame(2, $metrics['elastic_attempts']);
        $this->assertSame(1, $metrics['elastic_retries']);
        $this->assertSame(1, $connection->rebuilds);
    }

    public function test_bulk_retries_retryable_client_response_statuses(): void
    {
        config(['elasticsearch.bulk_retry_delays_ms' => [1]]);
        Log::spy();

        [$service] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient(
                $this->elasticResponse(['error' => 'too many requests'], 429),
                $this->elasticResponse(),
            ),
        );
        $statement = Statement::factory()->create(['id' => 1102]);

        $metrics = $service->benchmarkBulkIndexStatements(new EloquentCollection([$statement]));

        $this->assertSame(2, $metrics['elastic_attempts']);
        $this->assertSame(1, $metrics['elastic_retries']);
    }

    public function test_bulk_does_not_retry_unretryable_client_response_statuses(): void
    {
        config(['elasticsearch.bulk_retry_delays_ms' => [1]]);

        [$service] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient($this->elasticResponse(['error' => 'bad request'], 400)),
        );

        $this->expectException(ClientResponseException::class);

        $service->bulkIndexStatements(new EloquentCollection([
            Statement::factory()->create(['id' => 1103]),
        ]));
    }

    public function test_bulk_does_not_retry_unretryable_runtime_exceptions(): void
    {
        config(['elasticsearch.bulk_retry_delays_ms' => [1]]);

        [$service] = $this->indexerWithHttpClient(
            new FakeElasticHttpClient(new RuntimeException('not retryable')),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not retryable');

        $service->bulkIndexStatements(new EloquentCollection([
            Statement::factory()->create(['id' => 1104]),
        ]));
    }

    public function test_raw_statement_row_to_searchable_array_handles_empty_malformed_and_scalar_json(): void
    {
        $service = app(StatementElasticIndexerService::class);

        $payload = $service->rawStatementRowToSearchableArray($this->rawStatementRow([
            'decision_visibility' => null,
            'content_type' => 'not-json',
            'category_specification' => '"scalar"',
            'category_addition' => '["CATEGORY_STATEMENT_OTHER","CATEGORY_STATEMENT_OTHER","CATEGORY_STATEMENT_SCAM"]',
            'content_date' => null,
            'created_at' => '',
            'platform_name' => null,
            'platform_uuid' => null,
        ]));

        $this->assertSame([], $payload['decision_visibility']);
        $this->assertSame([], $payload['content_type']);
        $this->assertSame([], $payload['category_specification']);
        $this->assertSame(['CATEGORY_STATEMENT_OTHER', 'CATEGORY_STATEMENT_SCAM'], $payload['category_addition']);
        $this->assertNull($payload['content_date']);
        $this->assertNull($payload['created_at']);
        $this->assertNull($payload['received_date']);
        $this->assertSame('deleted-name-42', $payload['platform_name']);
        $this->assertSame('deleted-uuid-42', $payload['platform_uuid']);
    }

    public function test_private_defensive_helpers_are_covered_through_reflection(): void
    {
        config(['elasticsearch.bulk_retry_delays_ms' => '5']);
        [$service, $httpClient] = $this->indexerWithHttpClient(new FakeElasticHttpClient);

        $this->invokePrivate($service, 'bulkSearchableArrays', [[]]);

        $this->assertSame([], $httpClient->requests);
        $this->assertSame([5], $this->invokePrivate($service, 'bulkRetryDelaysMs'));
        $this->assertNull($this->invokePrivate($service, 'bulkExceptionStatus', [new ThrowingResponseStatusException]));
    }

    private function normalize(array $payload): array
    {
        return json_decode(json_encode($payload, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{0: StatementElasticIndexerService, 1: FakeElasticHttpClient, 2: FakeElasticConnectionService}
     */
    private function indexerWithHttpClient(FakeElasticHttpClient $httpClient): array
    {
        return $this->indexerWithHttpClients($httpClient);
    }

    /**
     * @return array{0: StatementElasticIndexerService, 1: FakeElasticHttpClient, 2: FakeElasticConnectionService}
     */
    private function indexerWithHttpClients(FakeElasticHttpClient ...$httpClients): array
    {
        $clients = array_map(
            fn (FakeElasticHttpClient $httpClient): Client => ClientBuilder::create()
                ->setHosts(['http://localhost:9200'])
                ->setHttpClient($httpClient)
                ->setLogger(new NullLogger)
                ->setRetries(0)
                ->setElasticMetaHeader(false)
                ->build(),
            $httpClients,
        );
        $connection = new FakeElasticConnectionService($clients);

        return [new StatementElasticIndexerService($connection), $httpClients[0], $connection];
    }

    private function elasticResponse(array $body = ['acknowledged' => true], int $status = 200): Response
    {
        return new Response($status, [
            'Content-Type' => 'application/json',
            'X-Elastic-Product' => 'Elasticsearch',
        ], json_encode($body, JSON_THROW_ON_ERROR));
    }

    private function rawStatementRow(array $overrides = []): stdClass
    {
        return (object) array_merge([
            'id' => '42',
            'uuid' => 'statement-uuid',
            'decision_visibility' => '["DECISION_VISIBILITY_CONTENT_REMOVED"]',
            'decision_visibility_other' => null,
            'decision_monetary' => null,
            'decision_monetary_other' => null,
            'decision_provision' => null,
            'decision_account' => null,
            'account_type' => null,
            'decision_ground' => null,
            'content_type' => '["CONTENT_TYPE_SYNTHETIC_MEDIA"]',
            'content_type_other' => null,
            'content_language' => 'en',
            'illegal_content_legal_ground' => null,
            'illegal_content_explanation' => null,
            'incompatible_content_ground' => null,
            'incompatible_content_explanation' => null,
            'source_type' => null,
            'source_identity' => null,
            'decision_facts' => null,
            'automated_detection' => Statement::AUTOMATED_DETECTION_YES,
            'automated_decision' => null,
            'category' => null,
            'category_addition' => '[]',
            'category_specification' => '[]',
            'platform_id' => '42',
            'platform_name' => 'Platform',
            'platform_uuid' => 'platform-uuid',
            'content_date' => '2035-01-02 00:00:00',
            'application_date' => '2035-01-03 00:00:00',
            'created_at' => '2035-01-04 12:34:56',
            'puid' => 'puid',
            'territorial_scope' => '',
            'method' => Statement::METHOD_API,
            'content_id_ean' => null,
        ], $overrides);
    }

    private function invokePrivate(object $object, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionClass($object);
        $reflectionMethod = $reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $arguments);
    }
}

class FakeElasticConnectionService extends StatementElasticConnectionService
{
    public int $rebuilds = 0;

    private int $clientIndex = 0;

    /**
     * @param  list<Client>  $clients
     */
    public function __construct(private readonly array $clients) {}

    public function client(): Client
    {
        return $this->clients[$this->clientIndex];
    }

    public function rebuildClient(): void
    {
        $this->rebuilds++;
        $this->clientIndex = min($this->clientIndex + 1, count($this->clients) - 1);
    }

    public function statementIndexName(): string
    {
        return 'test_statement_index';
    }
}

class FakeElasticHttpClient implements ClientInterface
{
    /**
     * @var list<RequestInterface>
     */
    public array $requests = [];

    /**
     * @var list<ResponseInterface|Throwable>
     */
    private array $queue;

    public function __construct(ResponseInterface|Throwable ...$queue)
    {
        $this->queue = $queue;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $next = array_shift($this->queue);

        if ($next === null) {
            throw new RuntimeException('No fake Elasticsearch response was queued.');
        }

        if ($next instanceof Throwable) {
            throw $next;
        }

        return $next;
    }
}

class ThrowingResponseStatusException extends RuntimeException
{
    public function getResponse(): never
    {
        throw new RuntimeException('Response status is unavailable.');
    }
}
