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
        $this->seed(); // 10 statements
        $total = $this->statement_query_service->query([])->count();
        $this->assertEquals(10, $total);
    }

    /**
     * @test
     */
    public function it_filters_on_automated_detection()
    {
        $this->seed(); // 10 statements
        $automated_count = $this->statement_query_service->query(['automated_detection' => ['Yes']])->count();
        $manual_count = $this->statement_query_service->query(['automated_detection' => ['No']])->count();

        $total = $automated_count + $manual_count;

        $this->assertEquals(10, $total);
    }

//    /**
//     * @test
//     */
//    public function it_filters_on_automated_takedown()
//    {
//        $this->seed(); // 10 statements
//        $automated_count = $this->statement_query_service->query(['automated_takedown' => ['Yes']])->count();
//        $manual_count = $this->statement_query_service->query(['automated_takedown' => ['No']])->count();
//
//        $total = $automated_count + $manual_count;
//
//        $this->assertEquals(10, $total);
//    }

    /**
     * @test
     */
    public function it_filters_on_automated_decision()
    {
        $this->seed(); // 10 statements
        $automated_count = $this->statement_query_service->query(['automated_decision' => ['Yes']])->count();
        $manual_count = $this->statement_query_service->query(['automated_decision' => ['No']])->count();

        $total = $automated_count + $manual_count;

        $this->assertEquals(10, $total);
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
            'platform_id' => [1]
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertEquals('select * from "statements" where exists (select * from "platforms" where "statements"."platform_id" = "platforms"."id" and "platforms"."id" in (?) and "platforms"."deleted_at" is null) and "statements"."deleted_at" is null', $sql);
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
    public function it_filters_on_source_type()
    {
        $filters = [
            'source_type' => array_keys(Statement::SOURCE_TYPES)
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertStringContainsString('select * from "statements" where "source_type" in (?', $sql);
    }

    /**
     * @test
     */
    public function it_filters_on_category()
    {
        $filters = [
            'category' => array_keys(Statement::STATEMENT_CATEGORIES)
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertStringContainsString('select * from "statements" where "category" in (?', $sql);
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
        $this->assertStringContainsString('"type" in (?', $sql);
    }
}
