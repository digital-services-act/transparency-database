<?php

namespace Tests\Feature\Services;

use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatementElasticSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatementElasticSearchService $statement_elastic_search_service;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->statement_elastic_search_service = app(StatementElasticSearchService::class);
        $this->assertNotNull($this->statement_elastic_search_service);
    }

    /**
     * @test
     */
    public function it_can_do_a_basic_query(): void
    {
        $filters = [];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertIsString($query);
        $this->assertEquals('*', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_automatic_detection(): void
    {
        $filters = [
            'automated_detection' => [Statement::AUTOMATED_DETECTION_YES],
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertIsString($query);
        $this->assertEquals('(automated_detection:true)', $query);

        $filters = [
            'automated_detection' => [Statement::AUTOMATED_DETECTION_YES, Statement::AUTOMATED_DETECTION_NO],
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(automated_detection:true OR automated_detection:false)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_automatic_decision(): void
    {
        $filters = [
            'automated_decision' => array_keys(Statement::AUTOMATED_DECISIONS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(automated_decision:AUTOMATED_DECISION_FULLY OR automated_decision:AUTOMATED_DECISION_PARTIALLY OR automated_decision:AUTOMATED_DECISION_NOT_AUTOMATED)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_source_type(): void
    {
        $filters = [
            'source_type' => array_keys(Statement::SOURCE_TYPES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(source_type:SOURCE_ARTICLE_16 OR source_type:SOURCE_TRUSTED_FLAGGER OR source_type:SOURCE_TYPE_OTHER_NOTIFICATION OR source_type:SOURCE_VOLUNTARY)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_s(): void
    {
        $filters = [
            's' => 'example',
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_visibility_other:"example" OR decision_monetary_other:"example" OR illegal_content_legal_ground:"example" OR illegal_content_explanation:"example" OR incompatible_content_ground:"example" OR incompatible_content_explanation:"example" OR decision_facts:"example" OR content_type_other:"example" OR source_identity:"example" OR uuid:"example" OR puid:"example" OR content_id_ean:"example")',
            $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_visibility(): void
    {
        $filters = [
            'decision_visibility' => array_keys(Statement::DECISION_VISIBILITIES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_visibility:DECISION_VISIBILITY_CONTENT_REMOVED OR decision_visibility:DECISION_VISIBILITY_CONTENT_DISABLED OR decision_visibility:DECISION_VISIBILITY_CONTENT_DEMOTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_INTERACTION_RESTRICTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_LABELLED OR decision_visibility:DECISION_VISIBILITY_OTHER)',
            $query);
    }

    /**
     * @test
     */
    public function it_filters_on_category_specification(): void
    {
        $filters = [
            'category_specification' => array_keys(Statement::KEYWORDS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('category_specification:KEYWORD_ANIMAL_HARM OR category_specification:', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_monetary(): void
    {
        $filters = [
            'decision_monetary' => array_keys(Statement::DECISION_MONETARIES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_monetary:DECISION_MONETARY_SUSPENSION OR decision_monetary:DECISION_MONETARY_TERMINATION OR decision_monetary:DECISION_MONETARY_OTHER)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_provision(): void
    {
        $filters = [
            'decision_provision' => array_keys(Statement::DECISION_PROVISIONS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_provision:DECISION_PROVISION_PARTIAL_SUSPENSION OR decision_provision:DECISION_PROVISION_TOTAL_SUSPENSION OR decision_provision:DECISION_PROVISION_PARTIAL_TERMINATION OR decision_provision:DECISION_PROVISION_TOTAL_TERMINATION)',
            $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_account(): void
    {
        $filters = [
            'decision_account' => array_keys(Statement::DECISION_ACCOUNTS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_account:DECISION_ACCOUNT_SUSPENDED OR decision_account:DECISION_ACCOUNT_TERMINATED)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_account_type(): void
    {
        $filters = [
            'account_type' => array_keys(Statement::ACCOUNT_TYPES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(account_type:ACCOUNT_TYPE_BUSINESS OR account_type:ACCOUNT_TYPE_PRIVATE)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_decision_grounds(): void
    {
        $filters = [
            'decision_ground' => array_keys(Statement::DECISION_GROUNDS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_ground:DECISION_GROUND_ILLEGAL_CONTENT OR decision_ground:DECISION_GROUND_INCOMPATIBLE_CONTENT)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_category(): void
    {
        $filters = [
            'category' => array_keys(Statement::STATEMENT_CATEGORIES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('category:STATEMENT_CATEGORY_ANIMAL_WELFARE OR', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_content_type(): void
    {
        $filters = [
            'content_type' => array_keys(Statement::CONTENT_TYPES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('content_type:CONTENT_TYPE_TEXT OR ', $query);
    }

    /**
     * @test
     */
    public function it_filters_only_real_platform_ids(): void
    {
        $filters = [
            'platform_id' => [99, 100],
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertNotEquals('(platform_id:99 OR platform_id:100)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_platform_id(): void
    {

        $platform_id_one = Platform::first()->id;
        $platform_id_two = Platform::nonDsa()->whereNotIn('id', [$platform_id_one])->inRandomOrder()->first()->id;

        $filters = [
            'platform_id' => [$platform_id_one, $platform_id_two],
        ];

        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(platform_id:'.$platform_id_one.' OR platform_id:'.$platform_id_two.')', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_territorial_scope(): void
    {
        $filters = [
            'territorial_scope' => ['BG', 'NL'],
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(territorial_scope:BG OR territorial_scope:NL)', $query);
    }

    /**
     * @test
     */
    public function it_filters_on_created_at(): void
    {
        $filters['created_at_start'] = '15-12-2020';
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('created_at:[2020-12-15T00:00:00 TO', $query);

        unset($filters['created_at_start']);
        $filters['created_at_end'] = '15-12-2020';
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('TO 2020-12-15T23:59:59]', $query);

        $filters['created_at_start'] = '20-12-2020';
        $filters['created_at_end'] = '21-12-2020';
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('2020-12-20T00:00:00 TO 2020-12-21T23:59:59]', $query);
    }

    /**
     * @test
     */
    public function start_and_end_dates_must_be_valid(): void
    {
        $filters['created_at_start'] = 'not a good date';
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringNotContainsString('created_at', $query);

        unset($filters['created_at_start']);
        $filters['created_at_end'] = 'holy cow a bad date';
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringNotContainsString('created_at', $query);

        $filters['created_at_start'] = 'seriously bad date';
        $filters['created_at_end'] = 'nothing good here';
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringNotContainsString('created_at', $query);
    }

    /**
     * @test
     */
    public function it_starts_a_count_query(): void
    {
        $result = $this->statement_elastic_search_service->startCountQuery();
        $this->assertEquals('SELECT CAST(count(*) AS BIGINT) as count FROM statement_index', $result);
    }

    /**
     * @test
     */
    public function it_extracts_a_count_query_result(): void
    {
        $fake_result = [
            'rows' => [
                [
                    0 => 666,
                ],
            ],
        ];

        $test = $this->statement_elastic_search_service->extractCountQueryResult($fake_result);
        $this->assertEquals(666, $test);
    }

    /**
     * @test
     */
    public function it_can_make_received_date_condition(): void
    {
        $now = Carbon::create(2024, 1, 29);
        $result = $this->statement_elastic_search_service->receivedDateCondition($now);
        $should_be = "received_date = '2024-01-29'";
        $this->assertEquals($should_be, $result);
    }

    /**
     * @test
     */
    public function it_can_make_received_date_range_condition(): void
    {
        $start = Carbon::create(2024, 1, 1);
        $end = Carbon::create(2024, 1, 2);
        $result = $this->statement_elastic_search_service->receivedDateRangeCondition($start, $end);
        $should_be = "received_date BETWEEN '2024-01-01' AND '2024-01-02'";
        $this->assertEquals($should_be, $result);
    }

    /**
     * @test
     */
    public function it_builds_wheres(): void
    {
        $conditions = [
            'platform_id = '. 666,
            $this->statement_elastic_search_service->receivedDateCondition(Carbon::create(2024, 1, 29)),
        ];
        $result = $this->statement_elastic_search_service->buildWheres($conditions);
        $should_be = " WHERE platform_id = 666 AND received_date = '2024-01-29'";
        $this->assertEquals($should_be, $result);

        $conditions = [
        ];
        $result = $this->statement_elastic_search_service->buildWheres($conditions);
        $should_be = '';
        $this->assertEquals($should_be, $result);
    }

    /**
     * @test
     */
    public function it_get_the_grand_total(): void
    {
        $cache = Cache::get('grand_total');
        $this->assertNull($cache);
        $result = $this->statement_elastic_search_service->grandTotal();
        $this->assertEquals(888, $result);
        $cache = Cache::get('grand_total');
        $this->assertNotNull($cache);
    }

    /**
     * @test
     */
    public function it_extracts_count_query_results(): void
    {
        $this->statement_elastic_search_service->setMockCountQueryAnswer(777);
        $result = $this->statement_elastic_search_service->extractCountQueryResult($this->statement_elastic_search_service->mockCountQueryResult());
        $this->assertEquals(777, $result);
    }

    /**
     * @test
     */
    public function it_handles_bad_count_query_results(): void
    {
        $result = $this->statement_elastic_search_service->extractCountQueryResult([['fruits' => ['bananas', 'oranges']]]);
        $this->assertEquals(0, $result);
    }

    /**
     * @test
     */
    public function it_can_get_the_top_categories(): void
    {
        $result = $this->statement_elastic_search_service->topCategories();
        $this->assertEquals(888, $result[4]['total']);

        $this->statement_elastic_search_service->setMockCountQueryAnswer(777);

        // This answer should be cached and not 777
        $result = $this->statement_elastic_search_service->topCategories();
        $this->assertNotEquals(777, $result[6]['total']);

        // run the no cache version
        $result = $this->statement_elastic_search_service->topCategoriesNoCache();
        $this->assertEquals(777, $result[6]['total']);

        // Forget it
        Cache::forget('top_categories');
        $result = $this->statement_elastic_search_service->topCategories();
        // Now it should be 777
        $this->assertEquals(777, $result[6]['total']);

    }

    /**
     * @test
     */
    public function it_can_get_the_top_decisions_visibility(): void
    {
        $result = $this->statement_elastic_search_service->topDecisionVisibilities();
        $this->assertEquals(888, $result[2]['total']);

        $this->statement_elastic_search_service->setMockCountQueryAnswer(777);

        // This answer should be cached and not 777
        $result = $this->statement_elastic_search_service->topDecisionVisibilities();
        $this->assertNotEquals(777, $result[3]['total']);

        // run the no cache version
        $result = $this->statement_elastic_search_service->topDecisionVisibilitiesNoCache();
        $this->assertEquals(777, $result[3]['total']);

        // Forget it
        Cache::forget('top_decisions_visibility');
        $result = $this->statement_elastic_search_service->topDecisionVisibilities();
        // Now it should be 777
        $this->assertEquals(777, $result[3]['total']);
    }

    /**
     * @test
     */
    public function it_gets_the_automated_decision_percentage(): void
    {
        $this->statement_elastic_search_service->setMockCountQueryAnswer(1000);
        $this->statement_elastic_search_service->grandTotal();

        $this->statement_elastic_search_service->setMockCountQueryAnswer(777);
        // this will round up to 78.
        $result = $this->statement_elastic_search_service->fullyAutomatedDecisionPercentage();
        $this->assertEquals(78, $result);

        Cache::forget('automated_decisions_percentage');
        $this->statement_elastic_search_service->setMockCountQueryAnswer(773); // This will round down to 77.
        $result = $this->statement_elastic_search_service->fullyAutomatedDecisionPercentage();
        $this->assertEquals(77, $result);
    }
}
