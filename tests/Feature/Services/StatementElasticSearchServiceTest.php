<?php

namespace Tests\Feature\Services;

use App\Models\Platform;
use App\Models\Statement;
use App\Services\StatementElasticSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_it_can_do_a_basic_query(): void
    {
        $filters = [];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertIsString($query);
        $this->assertEquals('*', $query);
    }

    public function test_it_filters_on_automatic_detection(): void
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

    public function test_it_filters_on_automatic_decision(): void
    {
        $filters = [
            'automated_decision' => array_keys(Statement::AUTOMATED_DECISIONS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(automated_decision:AUTOMATED_DECISION_FULLY OR automated_decision:AUTOMATED_DECISION_PARTIALLY OR automated_decision:AUTOMATED_DECISION_NOT_AUTOMATED)', $query);
    }

    public function test_it_filters_on_source_type(): void
    {
        $filters = [
            'source_type' => array_keys(Statement::SOURCE_TYPES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(source_type:SOURCE_ARTICLE_16 OR source_type:SOURCE_TRUSTED_FLAGGER OR source_type:SOURCE_TYPE_OTHER_NOTIFICATION OR source_type:SOURCE_VOLUNTARY)', $query);
    }

    public function test_it_filters_on_s(): void
    {
        $filters = [
            's' => 'example',
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(example)', $query);
    }

    public function test_it_filters_on_decision_visibility(): void
    {
        $filters = [
            'decision_visibility' => array_keys(Statement::DECISION_VISIBILITIES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_visibility:DECISION_VISIBILITY_CONTENT_REMOVED OR decision_visibility:DECISION_VISIBILITY_CONTENT_DISABLED OR decision_visibility:DECISION_VISIBILITY_CONTENT_DEMOTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_INTERACTION_RESTRICTED OR decision_visibility:DECISION_VISIBILITY_CONTENT_LABELLED OR decision_visibility:DECISION_VISIBILITY_OTHER)',
            $query);
    }

    public function test_it_filters_on_category_specification(): void
    {
        $filters = [
            'category_specification' => array_keys(Statement::KEYWORDS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('category_specification:KEYWORD_ANIMAL_HARM OR category_specification:', $query);
    }

    public function test_it_filters_on_decision_monetary(): void
    {
        $filters = [
            'decision_monetary' => array_keys(Statement::DECISION_MONETARIES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_monetary:DECISION_MONETARY_SUSPENSION OR decision_monetary:DECISION_MONETARY_TERMINATION OR decision_monetary:DECISION_MONETARY_OTHER)', $query);
    }

    public function test_it_filters_on_decision_provision(): void
    {
        $filters = [
            'decision_provision' => array_keys(Statement::DECISION_PROVISIONS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_provision:DECISION_PROVISION_PARTIAL_SUSPENSION OR decision_provision:DECISION_PROVISION_TOTAL_SUSPENSION OR decision_provision:DECISION_PROVISION_PARTIAL_TERMINATION OR decision_provision:DECISION_PROVISION_TOTAL_TERMINATION)',
            $query);
    }

    public function test_it_filters_on_decision_account(): void
    {
        $filters = [
            'decision_account' => array_keys(Statement::DECISION_ACCOUNTS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_account:DECISION_ACCOUNT_SUSPENDED OR decision_account:DECISION_ACCOUNT_TERMINATED)', $query);
    }

    public function test_it_filters_on_account_type(): void
    {
        $filters = [
            'account_type' => array_keys(Statement::ACCOUNT_TYPES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(account_type:ACCOUNT_TYPE_BUSINESS OR account_type:ACCOUNT_TYPE_PRIVATE)', $query);
    }

    public function test_it_filters_on_decision_grounds(): void
    {
        $filters = [
            'decision_ground' => array_keys(Statement::DECISION_GROUNDS),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(decision_ground:DECISION_GROUND_ILLEGAL_CONTENT OR decision_ground:DECISION_GROUND_INCOMPATIBLE_CONTENT)', $query);
    }

    public function test_it_filters_on_category(): void
    {
        $filters = [
            'category' => array_keys(Statement::STATEMENT_CATEGORIES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('category:STATEMENT_CATEGORY_ANIMAL_WELFARE OR', $query);
    }

    public function test_it_filters_on_content_type(): void
    {
        $filters = [
            'content_type' => array_keys(Statement::CONTENT_TYPES),
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertStringContainsString('content_type:CONTENT_TYPE_TEXT OR ', $query);
    }

    public function test_it_filters_only_real_platform_ids(): void
    {
        $filters = [
            'platform_id' => [99, 100],
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertNotEquals('(platform_id:99 OR platform_id:100)', $query);
    }

    public function test_it_filters_on_platform_id(): void
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

    public function test_it_filters_on_territorial_scope(): void
    {
        $filters = [
            'territorial_scope' => ['BG', 'NL'],
        ];
        $query = $this->statement_elastic_search_service->buildQuery($filters);
        $this->assertNotNull($query);
        $this->assertEquals('(territorial_scope:BG OR territorial_scope:NL)', $query);
    }

    public function test_it_filters_on_created_at(): void
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

    public function test_start_and_end_dates_must_be_valid(): void
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
}
