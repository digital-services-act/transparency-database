<?php

namespace Tests\Feature\Services;

use App\Models\Platform;
use App\Services\DayArchiveQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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

    public function it_filters_on_uuid(): void
    {
        $platform = Platform::first();
        $query = $this->day_archive_query_service->query([
            'uuid' => $platform->uuid,
        ]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" = ?', $sql);
    }
}
