<?php

namespace Tests\Support;

use App\Services\StatementElasticAggregationService;
use App\Services\StatementElasticConnectionService;
use App\Services\StatementElasticIndexerService;
use App\Services\StatementElasticSearchService;
use App\Services\StatementElasticStatsService;
use App\Services\StatementElasticToolsService;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Foundation\Application;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Throwable;

class ElasticMocker
{
    private FakeElasticHttpClient $httpClient;

    private FakeElasticConnectionService $connection;

    private function __construct(private readonly Application $app)
    {
        $this->httpClient = new FakeElasticHttpClient;
        $this->connection = new FakeElasticConnectionService(
            ClientBuilder::create()
                ->setHosts(['http://elastic.test:9200'])
                ->setHttpClient($this->httpClient)
                ->setLogger(new NullLogger)
                ->setRetries(0)
                ->setElasticMetaHeader(false)
                ->build(),
        );

        $this->bindConnection();
    }

    public static function fake(?Application $app = null): self
    {
        return new self($app ?? app());
    }

    public function searchReturns(array $body, int $status = 200): self
    {
        return $this->response($body, $status);
    }

    public function sqlReturns(array $body, int $status = 200): self
    {
        return $this->response($body, $status);
    }

    public function sqlCountReturns(int $count, int $status = 200): self
    {
        return $this->sqlReturns([
            'rows' => [
                [
                    $count,
                ],
            ],
        ], $status);
    }

    public function sqlRowsReturn(array $rows, int $status = 200): self
    {
        return $this->sqlReturns([
            'rows' => $rows,
        ], $status);
    }

    public function indexReturns(array $body = ['result' => 'created'], int $status = 200): self
    {
        return $this->response($body, $status);
    }

    public function bulkReturns(array $body = ['errors' => false], int $status = 200): self
    {
        return $this->response($body, $status);
    }

    public function deleteByQueryReturns(array $body = ['task' => 'test-task-id'], int $status = 200): self
    {
        return $this->response($body, $status);
    }

    public function indicesStatsReturns(array $body, int $status = 200): self
    {
        return $this->response($body, $status);
    }

    public function exists(bool $exists = true): self
    {
        return $this->response([], $exists ? 200 : 404);
    }

    public function acknowledged(bool $acknowledged = true): self
    {
        return $this->response(['acknowledged' => $acknowledged]);
    }

    public function createAliasSucceeds(bool $acknowledged = true): self
    {
        return $this
            ->exists()
            ->exists(false)
            ->acknowledged($acknowledged);
    }

    public function deleteAliasSucceeds(bool $acknowledged = true): self
    {
        return $this
            ->exists()
            ->exists()
            ->acknowledged($acknowledged);
    }

    public function swapAliasSucceeds(bool $acknowledged = true): self
    {
        return $this
            ->exists()
            ->exists()
            ->exists()
            ->exists(false)
            ->acknowledged($acknowledged);
    }

    public function deleteIndexSucceeds(bool $acknowledged = true): self
    {
        return $this
            ->exists()
            ->acknowledged($acknowledged);
    }

    public function removeDocumentSucceeds(string $result = 'deleted', ?int $version = null): self
    {
        return $this
            ->exists()
            ->response(array_filter([
                'result' => $result,
                '_version' => $version,
            ], static fn ($value): bool => $value !== null));
    }

    public function indexSettingsReturns(string $index, array $settings): self
    {
        return $this
            ->exists()
            ->response([
                $index => [
                    'settings' => $settings,
                ],
            ]);
    }

    public function indexInfoReturns(
        string $index,
        string $uuid = 'test-uuid',
        int $documents = 1000,
        int $sizeBytes = 1024,
        array $properties = ['id' => ['type' => 'long']],
        array $shards = [],
        array $aliases = [],
    ): self {
        return $this
            ->exists()
            ->indicesStatsReturns([
                'indices' => [
                    $index => [
                        'uuid' => $uuid,
                        'primaries' => [
                            'docs' => [
                                'count' => $documents,
                            ],
                        ],
                        'total' => [
                            'store' => [
                                'size_in_bytes' => $sizeBytes,
                            ],
                        ],
                    ],
                ],
            ])
            ->response([
                $index => [
                    'mappings' => [
                        'properties' => $properties,
                    ],
                ],
            ])
            ->response($shards)
            ->response([
                $index => [
                    'aliases' => array_fill_keys($aliases, []),
                ],
            ]);
    }

    public function refreshIntervalUpdateSucceeds(string $index, string $previousInterval, bool $acknowledged = true): self
    {
        return $this
            ->indexSettingsReturns($index, [
                'index' => [
                    'refresh_interval' => $previousInterval,
                ],
            ])
            ->acknowledged($acknowledged);
    }

    public function bulkModeUpdateSucceeds(
        string $index,
        array $currentSettings,
        bool $acknowledged = true,
        bool $refresh = false,
    ): self {
        $this
            ->indexSettingsReturns($index, [
                'index' => $currentSettings,
            ])
            ->acknowledged($acknowledged);

        if ($refresh) {
            $this->acknowledged();
        }

        return $this;
    }

    public function tasksListReturns(array $nodes): self
    {
        return $this->response(['nodes' => $nodes]);
    }

    public function tasksReturn(array $tasks, string $node = 'node_1'): self
    {
        return $this->tasksListReturns([
            $node => [
                'tasks' => $tasks,
            ],
        ]);
    }

    public function tasksCancelReturns(array $body = ['nodes' => []]): self
    {
        return $this->response($body);
    }

    public function uuidSearchReturns(int $id): self
    {
        return $this->searchReturns([
            'hits' => [
                'hits' => [
                    [
                        '_source' => [
                            'id' => $id,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param  list<string|int>  $ids
     */
    public function puidSearchReturns(array $ids): self
    {
        return $this->searchReturns([
            'hits' => [
                'hits' => array_map(static fn (string|int $id): array => [
                    '_source' => [
                        'id' => (int) $id,
                    ],
                    '_id' => $id,
                ], $ids),
            ],
        ]);
    }

    public function aggregateBucketsReturn(array $buckets): self
    {
        return $this->searchReturns([
            'aggregations' => [
                'composite_buckets' => [
                    'buckets' => $buckets,
                ],
            ],
        ]);
    }

    /**
     * Queues the response for StatementElasticToolsService::getIndexList().
     *
     * @param  list<string>  $indices
     */
    public function indexListReturns(array $indices): self
    {
        $body = ['indices' => []];

        foreach ($indices as $index) {
            $body['indices'][$index] = [];
        }

        return $this->indicesStatsReturns($body);
    }

    public function response(array $body, int $status = 200): self
    {
        $this->httpClient->queue(
            new Response($status, [
                'Content-Type' => 'application/json',
                'X-Elastic-Product' => 'Elasticsearch',
            ], json_encode($body, JSON_THROW_ON_ERROR)),
        );

        return $this;
    }

    public function exception(Throwable $exception): self
    {
        $this->httpClient->queue($exception);

        return $this;
    }

    /**
     * @return list<RequestInterface>
     */
    public function requests(): array
    {
        return $this->httpClient->requests;
    }

    private function bindConnection(): void
    {
        foreach ([
            StatementElasticAggregationService::class,
            StatementElasticSearchService::class,
            StatementElasticStatsService::class,
            StatementElasticIndexerService::class,
            StatementElasticToolsService::class,
            StatementElasticConnectionService::class,
        ] as $abstract) {
            $this->app->forgetInstance($abstract);
        }

        $this->app->instance(StatementElasticConnectionService::class, $this->connection);
    }
}

class FakeElasticConnectionService extends StatementElasticConnectionService
{
    public function __construct(private readonly Client $client) {}

    public function client(): Client
    {
        return $this->client;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function statementIndexName(): string
    {
        return 'statement_index';
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
    private array $queue = [];

    public function queue(ResponseInterface|Throwable $response): void
    {
        $this->queue[] = $response;
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
