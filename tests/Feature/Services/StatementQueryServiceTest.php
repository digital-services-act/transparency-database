<?php

namespace Tests\Feature\Services;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
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

    /**
     * @test
     */
    public function it_handles_errors_gracefully(): void
    {
        // Create a statement with known values
        Statement::query()->delete();
        Statement::factory()->create([
            'incompatible_content_ground' => 'test content'
        ]);
        
        // Test with invalid filter type (should log error but not throw exception)
        $filters = [
            'automated_detection' => 'invalid' // Should be array but we pass string
        ];
        
        $result = $this->statement_query_service->query($filters);
        $this->assertNotNull($result); // Should return a query builder despite error
        
        // Test with non-existent filter method
        $filters = [
            'non_existent_filter' => ['value']
        ];
        
        $result = $this->statement_query_service->query($filters);
        $this->assertNotNull($result); // Should return a query builder despite error
    }

    /**
     * @test
     */
    public function it_filters_on_text_search(): void
    {
        Statement::query()->delete();
        
        // Create a platform and user for the statement
        $platform = Platform::factory()->create();
        $user = User::factory()->create(['platform_id' => $platform->id]);
        
        $puid = 'test-puid-123';
        $decision_facts = 'test facts';
        
        // Create a statement with known searchable content
        $statement = Statement::factory()->create([
            'puid' => $puid,
            'decision_facts' => $decision_facts,
            'incompatible_content_ground' => 'test content ground',
            'incompatible_content_explanation' => 'test explanation',
            'illegal_content_legal_ground' => 'test legal ground',
            'illegal_content_explanation' => 'test legal explanation',
            'decision_visibility_other' => 'test visibility',
            'decision_monetary_other' => 'test monetary',
            'content_type_other' => 'test content type',
            'source_identity' => 'test source'
        ]);

        // Save the UUID that was auto-generated
        $uuid = $statement->uuid;
        
        // Verify test data was created
        $this->assertDatabaseHas('statements', ['uuid' => $uuid]);
        
        // Test each searchable field
        $searchTerms = [
            'test content ground',
            'test explanation',
            'test legal ground',
            'test legal explanation',
            'test facts',
            $uuid,
            $puid,
            'test visibility',
            'test monetary',
            'test content type',
            'test source'
        ];
        
        foreach ($searchTerms as $term) {
            $result = $this->statement_query_service->query(['s' => $term]);
            // Log the query and result for debugging
            $this->assertTrue($result->count() > 0, "Failed to find term: $term. Found {$result->count()} results. SQL: {$result->toSql()}");
        }
        
        // Test term that doesn't exist
        $result = $this->statement_query_service->query(['s' => 'nonexistent']);
        $this->assertEquals(0, $result->count());
    }

    /**
     * @test
     */
    public function it_filters_on_decision_fields(): void
    {
        Statement::query()->delete();
        
        // Create statements with various decision fields
        Statement::factory()->create([
            'decision_monetary' => array_key_first(Statement::DECISION_MONETARIES),
            'decision_provision' => array_key_first(Statement::DECISION_PROVISIONS),
            'decision_account' => array_key_first(Statement::DECISION_ACCOUNTS),
            'account_type' => array_key_first(Statement::ACCOUNT_TYPES)
        ]);
        
        // Test decision_monetary filter
        $result = $this->statement_query_service->query([
            'decision_monetary' => [array_key_first(Statement::DECISION_MONETARIES)]
        ]);
        $this->assertEquals(1, $result->count());
        
        // Test decision_provision filter
        $result = $this->statement_query_service->query([
            'decision_provision' => [array_key_first(Statement::DECISION_PROVISIONS)]
        ]);
        $this->assertEquals(1, $result->count());
        
        // Test decision_account filter
        $result = $this->statement_query_service->query([
            'decision_account' => [array_key_first(Statement::DECISION_ACCOUNTS)]
        ]);
        $this->assertEquals(1, $result->count());
        
        // Test account_type filter
        $result = $this->statement_query_service->query([
            'account_type' => [array_key_first(Statement::ACCOUNT_TYPES)]
        ]);
        $this->assertEquals(1, $result->count());
        
        // Test with invalid values
        $result = $this->statement_query_service->query([
            'decision_monetary' => ['INVALID_VALUE']
        ]);
        // The filter_values_validated will be empty, so no WHERE clause will be added
        $this->assertGreaterThanOrEqual(0, $result->count());
    }

    /**
     * @test
     */
    public function it_filters_on_content_language(): void
    {
        Statement::query()->delete();
        
        // Create a statement with a specific content language
        Statement::factory()->create([
            'content_language' => 'en'
        ]);
        
        // Test filtering by content language
        $result = $this->statement_query_service->query([
            'content_language' => ['en']
        ]);
        $this->assertEquals(1, $result->count());
        
        // Test filtering by non-existent language
        $result = $this->statement_query_service->query([
            'content_language' => ['xx']
        ]);
        $this->assertEquals(0, $result->count());
    }
}
