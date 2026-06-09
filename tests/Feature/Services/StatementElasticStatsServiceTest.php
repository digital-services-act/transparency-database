<?php

namespace Tests\Feature\Services;

use App\Services\StatementElasticStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class StatementElasticStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatementElasticStatsService $statement_elastic_stats_service;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->statement_elastic_stats_service = app(StatementElasticStatsService::class);
        $this->assertNotNull($this->statement_elastic_stats_service);
    }

    public function test_it_starts_a_count_query(): void
    {
        $result = $this->statement_elastic_stats_service->startCountQuery();
        $this->assertEquals('SELECT CAST(count(*) AS BIGINT) as count FROM statement_index', $result);
    }

    public function test_it_extracts_a_count_query_result(): void
    {
        $fake_result = [
            'rows' => [
                [
                    0 => 666,
                ],
            ],
        ];

        $test = $this->statement_elastic_stats_service->extractCountQueryResult($fake_result);
        $this->assertEquals(666, $test);
    }

    public function test_it_can_make_received_date_condition(): void
    {
        $now = Carbon::create(2024, 1, 29);
        $result = $this->statement_elastic_stats_service->receivedDateCondition($now);
        $should_be = "received_date = '2024-01-29'";
        $this->assertEquals($should_be, $result);
    }

    public function test_it_can_make_received_date_range_condition(): void
    {
        $start = Carbon::create(2024, 1, 1);
        $end = Carbon::create(2024, 1, 2);
        $result = $this->statement_elastic_stats_service->receivedDateRangeCondition($start, $end);
        $should_be = "received_date BETWEEN '2024-01-01' AND '2024-01-02'";
        $this->assertEquals($should_be, $result);
    }

    public function test_it_builds_wheres(): void
    {
        $conditions = [
            'platform_id = '. 666,
            $this->statement_elastic_stats_service->receivedDateCondition(Carbon::create(2024, 1, 29)),
        ];
        $result = $this->statement_elastic_stats_service->buildWheres($conditions);
        $should_be = " WHERE platform_id = 666 AND received_date = '2024-01-29'";
        $this->assertEquals($should_be, $result);

        $conditions = [
        ];
        $result = $this->statement_elastic_stats_service->buildWheres($conditions);
        $should_be = '';
        $this->assertEquals($should_be, $result);
    }

    public function test_it_get_the_grand_total(): void
    {
        $cache = Cache::get('grand_total');
        $this->assertNull($cache);
        $result = $this->statement_elastic_stats_service->grandTotal();
        $this->assertEquals(888, $result);
        $cache = Cache::get('grand_total');
        $this->assertNotNull($cache);
    }

    public function test_it_extracts_count_query_results(): void
    {
        $this->statement_elastic_stats_service->setMockCountQueryAnswer(777);
        $result = $this->statement_elastic_stats_service->extractCountQueryResult($this->statement_elastic_stats_service->mockCountQueryResult());
        $this->assertEquals(777, $result);
    }

    public function test_it_handles_bad_count_query_results(): void
    {
        $result = $this->statement_elastic_stats_service->extractCountQueryResult([['fruits' => ['bananas', 'oranges']]]);
        $this->assertEquals(0, $result);
    }

    public function test_it_can_get_the_top_categories(): void
    {
        $result = $this->statement_elastic_stats_service->topCategories();
        $this->assertEquals(888, $result[4]['total']);

        $this->statement_elastic_stats_service->setMockCountQueryAnswer(777);

        // This answer should be cached and not 777
        $result = $this->statement_elastic_stats_service->topCategories();
        $this->assertNotEquals(777, $result[6]['total']);

        // run the no cache version
        $result = $this->statement_elastic_stats_service->topCategoriesNoCache();
        $this->assertEquals(777, $result[6]['total']);

        // Forget it
        Cache::forget('top_categories');
        $result = $this->statement_elastic_stats_service->topCategories();
        // Now it should be 777
        $this->assertEquals(777, $result[6]['total']);

    }

    public function test_it_can_get_the_top_decisions_visibility(): void
    {
        $result = $this->statement_elastic_stats_service->topDecisionVisibilities();
        $this->assertEquals(888, $result[2]['total']);

        $this->statement_elastic_stats_service->setMockCountQueryAnswer(777);

        // This answer should be cached and not 777
        $result = $this->statement_elastic_stats_service->topDecisionVisibilities();
        $this->assertNotEquals(777, $result[3]['total']);

        // run the no cache version
        $result = $this->statement_elastic_stats_service->topDecisionVisibilitiesNoCache();
        $this->assertEquals(777, $result[3]['total']);

        // Forget it
        Cache::forget('top_decisions_visibility');
        $result = $this->statement_elastic_stats_service->topDecisionVisibilities();
        // Now it should be 777
        $this->assertEquals(777, $result[3]['total']);
    }

    public function test_it_gets_the_automated_decision_percentage(): void
    {
        $this->statement_elastic_stats_service->setMockCountQueryAnswer(1000);
        $this->statement_elastic_stats_service->grandTotal();

        $this->statement_elastic_stats_service->setMockCountQueryAnswer(777);
        // this will round up to 78.
        $result = $this->statement_elastic_stats_service->fullyAutomatedDecisionPercentage();
        $this->assertEquals(78, $result);

        Cache::forget('automated_decisions_percentage');
        $this->statement_elastic_stats_service->setMockCountQueryAnswer(773); // This will round down to 77.
        $result = $this->statement_elastic_stats_service->fullyAutomatedDecisionPercentage();
        $this->assertEquals(77, $result);
    }
}
