<?php

namespace Tests\Feature\Services;


use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class StatementSearchServiceTest extends TestCase
{

    use RefreshDatabase;

    protected StatementSearchService $statement_search_service;

    public function setUp(): void
    {
        parent::setUp();
        $this->statement_search_service = app(StatementSearchService::class);
        $this->assertNotNull($this->statement_search_service);
    }

    /**
     * @test
     */
    public function it_can_do_a_basic_query()
    {
        $filters = [];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('*', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_automatic_detection()
    {
        $filters = [
            'automated_detection' => [Statement::AUTOMATED_DETECTION_YES]
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(automated_detection:true)', $query);

        $filters = [
            'automated_detection' => [Statement::AUTOMATED_DETECTION_YES, Statement::AUTOMATED_DETECTION_NO]
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(automated_detection:true OR automated_detection:false)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_automatic_decision()
    {
        $filters = [
            'automated_decision' => array_keys(Statement::AUTOMATED_DECISIONS),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(automated_decision:AUTOMATED_DECISION_FULLY OR automated_decision:AUTOMATED_DECISION_PARTIALLY OR automated_decision:AUTOMATED_DECISION_NOT_AUTOMATED)', $query);

    }

    /**
     * @test
     */
    public function it_filters_on_s()
    {
        $filters = [
            's' => 'example'
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(decision_visibility_other:"example" OR decision_monetary_other:"example" OR illegal_content_legal_ground:"example" OR illegal_content_explanation:"example" OR incompatible_content_ground:"example" OR incompatible_content_explanation:"example" OR decision_facts:"example" OR content_type_other:"example" OR source_identity:"example" OR uuid:"example" OR puid:"example")', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_visibility()
    {
        $filters = [
            'decision_visibility' => array_keys(Statement::DECISION_VISIBILITIES),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(decision_visibility:DECISION_VISIBILITY_CONTENT_REMOVED OR decision_visibility:DECISION_VISIBILITY_CONTENT_DISABLED OR decision_visibility:DECISION_VISIBILITY_CONTENT_DEMOTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_INTERACTION_RESTRICTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_LABELLED OR decision_visibility:DECISION_VISIBILITY_OTHER)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_category_specification()
    {
        $filters = [
            'category_specification' => array_keys(Statement::KEYWORDS),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertStringContainsString('category_specification:KEYWORD_ANIMAL_HARM OR category_specification:', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_monetary()
    {
        $filters = [
            'decision_monetary' => array_keys(Statement::DECISION_MONETARIES),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(decision_monetary:DECISION_MONETARY_SUSPENSION OR decision_monetary:DECISION_MONETARY_TERMINATION OR decision_monetary:DECISION_MONETARY_OTHER)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_provision()
    {
        $filters = [
            'decision_provision' => array_keys(Statement::DECISION_PROVISIONS),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(decision_provision:DECISION_PROVISION_PARTIAL_SUSPENSION OR decision_provision:DECISION_PROVISION_TOTAL_SUSPENSION OR decision_provision:DECISION_PROVISION_PARTIAL_TERMINATION OR decision_provision:DECISION_PROVISION_TOTAL_TERMINATION)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_account()
    {
        $filters = [
            'decision_account' => array_keys(Statement::DECISION_ACCOUNTS),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(decision_account:DECISION_ACCOUNT_SUSPENDED OR decision_account:DECISION_ACCOUNT_TERMINATED)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_account_type()
    {
        $filters = [
            'account_type' => array_keys(Statement::ACCOUNT_TYPES),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(account_type:ACCOUNT_TYPE_BUSINESS OR account_type:ACCOUNT_TYPE_PRIVATE)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_grounds()
    {
        $filters = [
            'decision_ground' => array_keys(Statement::DECISION_GROUNDS),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(decision_ground:DECISION_GROUND_ILLEGAL_CONTENT OR decision_ground:DECISION_GROUND_INCOMPATIBLE_CONTENT)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_category()
    {
        $filters = [
            'category' => array_keys(Statement::STATEMENT_CATEGORIES),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertStringContainsString('category:STATEMENT_CATEGORY_ANIMAL_WELFARE OR', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_content_type()
    {
        $filters = [
            'content_type' => array_keys(Statement::CONTENT_TYPES),
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertStringContainsString('content_type:CONTENT_TYPE_TEXT OR ', $query);
    }

    /**
     * @test
     */
    public function it_filters_only_real_platform_ids()
    {
        $filters = [
            'platform_id' => [99,100],
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertNotEquals('(platform_id:99 OR platform_id:100)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_platform_id()
    {
        $this->setUpFullySeededDatabase();
        $platform_id_one = Platform::first()->id;
        $platform_id_two = Platform::nonDsa()->whereNotIn('id',  [$platform_id_one])->inRandomOrder()->first()->id;

        $filters = [
            'platform_id' => [$platform_id_one, $platform_id_two],
        ];


        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(platform_id:'.$platform_id_one.' OR platform_id:'.$platform_id_two.')', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_territorial_scope()
    {
        $filters = [
            'territorial_scope' => ['BG','NL'],
        ];
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertEquals('(territorial_scope:BG OR territorial_scope:NL)', $query);
    }

    /**
     * @test
     */
    public function if_filters_on_created_at()
    {
        $filters['created_at_start'] = '15-12-2020';
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertStringContainsString('created_at:[2020-12-15T00:00:00 TO', $query);

        $filters['created_at_end'] = '15-12-2020';
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertStringContainsString('TO 2020-12-15T23:59:59]', $query);

        $filters['created_at_start'] = '20-12-2020';
        $filters['created_at_end'] = '21-12-2020';
        $search = $this->statement_search_service->query($filters);
        $this->assertNotNull($search);
        $query = $search->query;
        $this->assertStringContainsString('2020-12-20T00:00:00 TO 2020-12-21T23:59:59]', $query);
    }



}
