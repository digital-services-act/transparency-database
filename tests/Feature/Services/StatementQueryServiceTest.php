<?php

namespace Tests\Feature\Services;

use App\Models\Statement;
use App\Services\StatementQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Services\StatementQueryService
 */
class StatementQueryServiceTest extends TestCase
{

    use RefreshDatabase;

    protected StatementQueryService $statement_query_service;

    #[\Override]protected function setUp(): void
    {
        parent::setUp();
        $this->statement_query_service = app(StatementQueryService::class);
        $this->assertNotNull($this->statement_query_service);
        
        // Clear any existing statements and create fresh test data
        Statement::query()->delete();
        Statement::factory(10)->create();
    }

    /**
     * @test
     */
    public function it_can_do_a_basic_query(): void
    {
         // 10 statements
        $total = $this->statement_query_service->query([])->count();
        $this->assertEquals(10, $total);
    }

    /**
     * @test
     */
    public function it_filters_on_automated_detection(): void
    {
         // 10 statements
        $automated_count = $this->statement_query_service->query(['automated_detection' => ['Yes']])->count();
        $manual_count = $this->statement_query_service->query(['automated_detection' => ['No']])->count();

        $total = $automated_count + $manual_count;

        $this->assertEquals(10, $total);
    }

    /**
     * @test
     */
    public function it_filters_on_automated_decision(): void
    {
         // 10 statements
        $fully_count = $this->statement_query_service->query(['automated_decision' => ['AUTOMATED_DECISION_FULLY']])->count();
        $partially_count = $this->statement_query_service->query(['automated_decision' => ['AUTOMATED_DECISION_PARTIALLY']])->count();
        $not_count = $this->statement_query_service->query(['automated_decision' => ['AUTOMATED_DECISION_NOT_AUTOMATED']])->count();

        $total = $fully_count + $partially_count + $not_count;

        $this->assertEquals(10, $total);
    }

    /**
     * @test
     */
    public function it_filters_on_territorial_scope(): void
    {
        $filters = [
            'territorial_scope' => ["FR", "DE", "NL", "XX"]
        ];
        // The XX should be filtered out as it's not a valid European country code
        $sql = $this->statement_query_service->query($filters)->toSql();
        $bindings = $this->statement_query_service->query($filters)->getBindings();
        
        // Check that we're using JSON extract and proper OR conditions
        $this->assertStringContainsString('json_extract', strtolower($sql));
        $this->assertCount(3, $bindings); // Should only have 3 bindings as XX is filtered out
        $this->assertEquals(['%"FR"%', '%"DE"%', '%"NL"%'], array_values($bindings));
    }

    /**
     * @test
     */
    public function it_filters_on_decision_visibility(): void
    {
        $filters = [
            'decision_visibility' => ['DECISION_VISIBILITY_CONTENT_REMOVED', 'DECISION_VISIBILITY_CONTENT_DISABLED']
        ];
        
        $sql = $this->statement_query_service->query($filters)->toSql();
        $bindings = $this->statement_query_service->query($filters)->getBindings();
        
        // Check that we're using JSON extract and proper OR conditions
        $this->assertStringContainsString('json_extract', strtolower($sql));
        $this->assertCount(2, $bindings);
        $this->assertEquals(
            ['%"DECISION_VISIBILITY_CONTENT_REMOVED"%', '%"DECISION_VISIBILITY_CONTENT_DISABLED"%'], 
            array_values($bindings)
        );
    }

    /**
     * @test
     */
    public function it_filters_on_content_type(): void
    {
        $filters = [
            'content_type' => ['CONTENT_TYPE_TEXT', 'CONTENT_TYPE_VIDEO', 'INVALID_TYPE']
        ];
        
        $sql = $this->statement_query_service->query($filters)->toSql();
        $bindings = $this->statement_query_service->query($filters)->getBindings();
        
        // Check that we're using JSON extract and proper OR conditions
        $this->assertStringContainsString('json_extract', strtolower($sql));
        $this->assertCount(2, $bindings); // Should only have 2 bindings as INVALID_TYPE is filtered out
        $this->assertEquals(['%"CONTENT_TYPE_TEXT"%', '%"CONTENT_TYPE_VIDEO"%'], array_values($bindings));
    }

    /**
     * @test
     */
    public function it_filters_on_category_specification(): void
    {
        // Clear existing data and create statements with known category specifications
        Statement::query()->delete();
        Statement::factory()->create([
            'category_specification' => ['KEYWORD_HATE_SPEECH', 'KEYWORD_OTHER']
        ]);
        Statement::factory()->create([
            'category_specification' => ['KEYWORD_DISINFORMATION', 'KEYWORD_HATE_SPEECH']
        ]);
        
        $filters = [
            'category_specification' => ['KEYWORD_HATE_SPEECH', 'KEYWORD_DISINFORMATION', 'INVALID_KEYWORD']
        ];
        
        $result = $this->statement_query_service->query($filters);
        $sql = $result->toSql();
        $bindings = $result->getBindings();
        
        // Check that we're using JSON extract and proper OR conditions
        $this->assertStringContainsString('json_extract', strtolower($sql));
        $this->assertCount(2, $bindings); // Should only have 2 bindings as INVALID_KEYWORD is filtered out
        $this->assertEquals(['%"KEYWORD_HATE_SPEECH"%', '%"KEYWORD_DISINFORMATION"%'], array_values($bindings));
        
        // Verify we get both statements that have either KEYWORD_HATE_SPEECH or KEYWORD_DISINFORMATION
        $this->assertEquals(2, $result->count());
    }

    /**
     * @test
     */
    public function it_filters_on_created_at_start(): void
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
    public function it_filters_on_created_at_end(): void
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
    public function it_filters_on_platform_id(): void
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
    public function it_filters_on_decision_ground(): void
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
    public function it_filters_on_source_type(): void
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
    public function it_filters_on_category(): void
    {
        $filters = [
            'category' => array_keys(Statement::STATEMENT_CATEGORIES)
        ];
        $sql = $this->statement_query_service->query($filters)->toSql();
        $this->assertStringContainsString('select * from "statements" where "category" in (?', $sql);
    }
}
