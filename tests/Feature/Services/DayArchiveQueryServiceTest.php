<?php

namespace Tests\Feature\Services;

use App\Models\Platform;
use App\Services\DayArchiveQueryService;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class DayArchiveQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DayArchiveQueryService $day_archive_query_service;

    private array $required_fields;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->day_archive_query_service = app(DayArchiveQueryService::class);
        $this->assertNotNull($this->day_archive_query_service);
    }

    public function test_it_builds_query(): void
    {
        $query = $this->day_archive_query_service->query([]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" is null', $sql);
    }

    public function test_it_filters_on_the_filters(): void
    {
        $platform = Platform::first();
        $query = $this->day_archive_query_service->query([
            'platform_id' => $platform->id,
            'from_date' => '16-12-2020',
            'to_date' => '16-12-2021',
        ]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" = ? and strftime(\'%Y-%m-%d\', "date") >= cast(? as text) and strftime(\'%Y-%m-%d\', "date") <= cast(? as text)', $sql);
    }

    public function test_it_ignores_malformed_dates_without_logging_errors(): void
    {
        Log::spy();

        $platform = Platform::first();
        $query = $this->day_archive_query_service->query([
            'platform_id' => $platform->id,
            'from_date' => '2026-07-08',
            'to_date' => '31-02-2026',
        ]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" = ?', $sql);
        Log::shouldNotHaveReceived('error');
    }

    public function test_it_ignores_american_date_filters_without_logging_errors(): void
    {
        Log::spy();

        $platform = Platform::first();
        $query = $this->day_archive_query_service->query([
            'platform_id' => $platform->id,
            'from_date' => '07/08/2026',
            'to_date' => '07/09/2026',
        ]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" = ?', $sql);
        Log::shouldNotHaveReceived('error');
    }

    public function test_it_ignores_non_string_date_filters_without_logging_errors(): void
    {
        Log::spy();

        $platform = Platform::first();
        $query = $this->day_archive_query_service->query([
            'platform_id' => $platform->id,
            'from_date' => ['16-12-2020'],
            'to_date' => ['16-12-2021'],
        ]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" = ?', $sql);
        Log::shouldNotHaveReceived('error');
    }

    public function test_it_filters_on_uuid(): void
    {
        $platform = Platform::first();
        $query = $this->day_archive_query_service->query([
            'uuid' => $platform->uuid,
        ]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" = ? and "platform_id" is null', $sql);
    }

    public function test_it_ignores_invalid_uuid_filters(): void
    {
        $query = $this->day_archive_query_service->query([
            'uuid' => 'not-a-uuid',
        ]);

        $this->assertSame(
            'select * from "day_archives" where "completed_at" is not null and "platform_id" is null',
            $query->toSql()
        );
    }

    public function test_it_logs_filter_exceptions_and_continues_building_the_query(): void
    {
        Log::spy();

        $originalResolver = Platform::getConnectionResolver();
        $resolver = new class($originalResolver) implements ConnectionResolverInterface
        {
            private int $connectionCalls = 0;

            public function __construct(private readonly ConnectionResolverInterface $delegate) {}

            public function connection($name = null)
            {
                $this->connectionCalls++;

                if ($this->connectionCalls === 2) {
                    throw new RuntimeException('Simulated platform query failure.');
                }

                return $this->delegate->connection($name);
            }

            public function getDefaultConnection(): string
            {
                return $this->delegate->getDefaultConnection();
            }

            public function setDefaultConnection($name): void
            {
                $this->delegate->setDefaultConnection($name);
            }
        };

        Platform::setConnectionResolver($resolver);

        try {
            $query = $this->day_archive_query_service->query([
                'platform_id' => 1,
            ]);

            $this->assertNotNull($query);
            Log::shouldHaveReceived('error')->once();
        } finally {
            Platform::setConnectionResolver($originalResolver);
        }
    }
}
