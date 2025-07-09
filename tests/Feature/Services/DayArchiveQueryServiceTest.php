<?php

namespace Tests\Feature\Services;

use App\Services\DayArchiveQueryService;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DayArchiveQueryServiceTest extends TestCase
{

    use RefreshDatabase;

    protected DayArchiveQueryService $day_archive_query_service;

    private array $required_fields;

    #[\Override]protected function setUp(): void
    {
        parent::setUp();
        $this->day_archive_query_service = app(DayArchiveQueryService::class);
        $this->assertNotNull($this->day_archive_query_service);
    }

    /**
     *
     * @test
     * @return void
     */
    public function it_builds_query(): void
    {
        $query = $this->day_archive_query_service->query([]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" is null', $sql);
    }

    /**
     *
     * @test
     * @return void
     */
    public function it_filters_on_the_filters(): void
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


    /**
     *
     * @test
     * @return void
     */
    public function it_throws_an_error_on_bad_dates_and_skips(): void
    {
        $platform = Platform::first();
        $query = $this->day_archive_query_service->query([
            'platform_id' => $platform->id,
            'from_date' => '1632-2020',
            'to_date' => '99-12021',
        ]);
        $this->assertNotNull($query);
        $sql = $query->toSql();
        $this->assertEquals('select * from "day_archives" where "completed_at" is not null and "platform_id" = ?', $sql);
    }

}
