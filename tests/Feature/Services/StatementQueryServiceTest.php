<?php

namespace Tests\Feature\Services;


use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class StatementQueryServiceTest extends TestCase
{

    use RefreshDatabase;

    protected StatementQueryService $statement_query_service;

    public function setUp(): void
    {
        $this->statement_query_service = app(StatementQueryService::class);
        $this->assertNotNull($this->statement_query_service);
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    /**
     * @test
     */
    public function it_can_do_a_basic_query()
    {
        $this->seed(); // 200 statements
        $total = $this->statement_query_service->query([])->count();
        $this->assertEquals(200, $total);
    }

    /**
     * @test
     */
    public function it_filters_on_automated_detection()
    {
        $this->seed(); // 200 statements
        $automated_count = $this->statement_query_service->query(['automated_detection' => ['Yes']])->count();
        $manual_count = $this->statement_query_service->query(['automated_detection' => ['No']])->count();

        $total = $automated_count + $manual_count;

        $this->assertEquals(200, $total);
    }

    /**
     * @test
     */
    public function it_filters_on_automated_takedown()
    {
        $this->seed(); // 200 statements
        $automated_count = $this->statement_query_service->query(['automated_takedown' => ['Yes']])->count();
        $manual_count = $this->statement_query_service->query(['automated_takedown' => ['No']])->count();

        $total = $automated_count + $manual_count;

        $this->assertEquals(200, $total);
    }

    /**
     * @test
     */
    public function it_filters_on_countries_list()
    {
        $filters = [
            'countries_list' => ["FR", "DE", "NL", "XX"]
        ];
        // The XX should be filtered out... ;)
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertEquals('select * from "statements" where "countries_list" LIKE ? and "countries_list" LIKE ? and "countries_list" LIKE ? and "statements"."deleted_at" is null', $sql);
    }

    /**
     * @test
     */
    public function it_filters_on_created_at_start()
    {
        $filters = [
            'created_at_start' => "20-5-2021"
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertEquals('select * from "statements" where "created_at" >= ? and "statements"."deleted_at" is null', $sql);
    }

    /**
     * @test
     */
    public function it_filters_on_created_at_end()
    {
        $filters = [
            'created_at_end' => "20-5-2021"
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertEquals('select * from "statements" where "created_at" <= ? and "statements"."deleted_at" is null', $sql);
    }

    /**
     * @test
     */
    public function it_filters_on_platform_id()
    {
        $filters = [
            'platform_id' => 1
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertEquals('select * from "statements" where exists (select * from "platforms" inner join "users" on "users"."platform_id" = "platforms"."id" where "statements"."user_id" = "users"."id" and "platforms"."id" = ? and "platforms"."deleted_at" is null and "users"."deleted_at" is null) and "statements"."deleted_at" is null', $sql);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_ground()
    {
        $filters = [
            'decision_ground' => array_keys(Statement::DECISION_GROUNDS)
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertStringContainsString('select * from "statements" where "decision_ground" in (?', $sql);
    }

    /**
     * @test
     */
    public function it_filters_on_platform_type()
    {
        $filters = [
            'platform_type' => array_keys(Platform::PLATFORM_TYPES)
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertStringContainsString('select * from "statements" ', $sql);
        $this->assertStringContainsString('"type" = ?', $sql);
    }
}
