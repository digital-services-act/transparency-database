<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\PlatformPuid;
use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;


class StatementMultipleAPIControllerTest extends TestCase
{
    use AdditionalAssertions;
    use RefreshDatabase;
    use WithFaker;

    private array $required_fields;

    private Statement $statement;

    /**
     * @param int $count
     *
     * @return array
     */
    public function createFullStatements($count = 5): array
    {
        $statements = Statement::factory()->count($count)->make()->toArray();

        foreach ($statements as &$statement) {
            $statement['puid'] = Str::uuid()->toString();
            $statement['content_type'] = $this->faker->randomElements(array_keys(Statement::CONTENT_TYPES), 2, false);
            unset($statement['permalink']);
            unset($statement['platform_name']);
            unset($statement['self']);
        }

        return $statements;
    }


    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->required_fields = [
            'decision_visibility' => [
                'DECISION_VISIBILITY_CONTENT_DISABLED',
                'DECISION_VISIBILITY_CONTENT_AGE_RESTRICTED'
            ],
            'decision_monetary' => null,
            'decision_provision' => null,
            'decision_account' => null,
            'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'puid' => 'TK421',
            'territorial_scope' => ['BE', 'DE', 'FR'],
            'source_type' => 'SOURCE_ARTICLE_16',
            'source_identity' => 'foo',
            'decision_facts' => 'decision and facts',
            'content_type' => ['CONTENT_TYPE_SYNTHETIC_MEDIA'],
            'automated_detection' => 'No',
            'automated_decision' => 'AUTOMATED_DECISION_PARTIALLY',
            'application_date' => '2023-05-18',
            'content_date' => '2023-05-18'
        ];
    }


    /**
     * @test
     */
    public function api_statements_store_requires_auth(): void
    {
        // Not signing in.
        $this->assertCount(10, Statement::all());
        $response = $this->post(route('api.v1.statements.store'), [$this->required_fields], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }


    /**
     * @test
     */
    public function api_statements_store_works(): void
    {
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);

        $create = 10;
        $sors = [];
        while ($create--) {
            $fields['puid'] = uniqid();
            $sors[] = $fields;
        }

        $response = $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertCount(20, Statement::all());
    }


    /**
     * @test
     */
    public function api_statements_store_validates(): void
    {
        $user = $this->signInAsContributor();

        $this->assertCount(10, Statement::all());

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);

        $create = 10;
        $sors = [];
        while ($create--) {
            $fields['puid'] = uniqid();
            $sors[] = $fields;
        }

        $sors[3]['content_language'] = 'XX';

        $response = $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertEquals('The selected content language is invalid.',
            $response->json('errors.statement_3.content_language.0'));

        $this->assertCount(10, Statement::all());
    }

    /**
     * @test
     */
    public function api_statements_store_detect_non_unique_in_call(): void
    {
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);

        $create = 10;
        $sors = [];
        while ($create--) {
            $fields['puid'] = uniqid();
            $sors[] = $fields;
        }

        $sors[0]['puid'] = $sors[5]['puid'];

        $response = $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertCount(10, Statement::all());
    }

    /**
     * @test
     */
    public function api_statements_store_detect_non_unique_in_cache(): void
    {
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);

        $create = 1;
        $sors = [];
        while ($create--) {
            $fields['puid'] = 'foo-bar-123';
            $sors[] = $fields;
        }

        $key = sprintf('puid-%s-foo-bar-123', $user->platform->id);
        $this->assertFalse(Cache::has($key));

        $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);

        $this->assertTrue(Cache::has($key));

        $this->assertCount(11, Statement::all());

        $response = $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @test
     */
    public function api_statements_store_detects_previous_puid(): void
    {
        $user = $this->signInAsAdmin();

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);

        $create = 10;
        $sors = [];
        while ($create--) {
            $fields['puid'] = uniqid();
            $sors[] = $fields;
        }

        $this->assertDatabaseCount(Statement::class, 10);
        $this->assertDatabaseCount(PlatformPuid::class, 0);

        $response = $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseCount(PlatformPuid::class, 10);
        $this->assertDatabaseCount(Statement::class, 20);

        $response = $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('existing_puids', $response->json('errors'));

        $this->assertCount(20, Statement::all());
    }

    /**
     * @test
     */
    public function api_statements_store_detects_previous_puid_from_database_and_refresh_cache(): void
    {
        $user = $this->signInAsAdmin();

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);


        $sors = [];
        $fields['puid'] = 'foo-bar-sor-in-database';
        $sors[] = $fields;

        $create = 10;
        while ($create--) {
            $fields['puid'] = uniqid();
            $sors[] = $fields;
        }

        $key = sprintf('puid-%s-foo-bar-sor-in-database', $user->platform->id);
        $this->assertFalse(Cache::has($key));

        PlatformPuid::factory([
            'puid' => 'foo-bar-sor-in-database',
            'platform_id' => $user->platform->id
        ])->create();


        $this->assertDatabaseCount(Statement::class, 10);
        $this->assertDatabaseCount(PlatformPuid::class, 1);

        $response = $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('existing_puids', $response->json('errors'));

        $this->assertDatabaseCount(PlatformPuid::class, 1);
        $this->assertCount(10, Statement::all());
        $this->assertTrue(Cache::has($key));
    }


    /**
     * @test
     */
    public function it_should_store_multiple_submissions_created_by_factory(): void
    {
        $user = $this->signInAsContributor();

        $statements = $this->createFullStatements(5);

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertCount(15, Statement::all());
    }

    /**
     * @test
     */
    public function it_should_require_decision_visibility_other_field_when_sending_multiple_statements(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(5);

        $statements[0]['decision_visibility'] = ['DECISION_VISIBILITY_OTHER'];
        $statements[0]['decision_visibility_other'] = 'required field';
        $statements[1]['decision_visibility'] = ['DECISION_VISIBILITY_OTHER'];
        unset($statements[1]['decision_visibility_other']);
        $statements[2]['decision_visibility'] = ['DECISION_VISIBILITY_OTHER'];
        $statements[2]['decision_visibility_other'] = null;
        $statements[3]['decision_visibility'] = ['DECISION_VISIBILITY_CONTENT_LABELLED'];
        $statements[3]['decision_visibility_other'] = null;
        $statements[4]['decision_visibility'] = ['DECISION_VISIBILITY_CONTENT_DEMOTED', 'DECISION_VISIBILITY_OTHER'];
        $statements[4]['decision_visibility_other'] = null;

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(3, $response->json('errors'));
        $this->assertEquals('The decision visibility other field is required.',
            $response->json('errors.statement_1.decision_visibility_other.0'));
        $this->assertEquals('The decision visibility other field is required.',
            $response->json('errors.statement_2.decision_visibility_other.0'));
        $this->assertEquals('The decision visibility other field is required.',
            $response->json('errors.statement_4.decision_visibility_other.0'));
        $this->assertCount(10, Statement::all());
    }

    /**
     * @test
     */
    public function it_should_require_descriptions_when_other_fields_are_sent_via_multiple_statements(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(5);

        $statements[0]['content_type'] = ['CONTENT_TYPE_OTHER'];
        $statements[0]['content_type_other'] = 'required field';
        $statements[1]['content_type'] = ['CONTENT_TYPE_OTHER'];
        unset($statements[1]['content_type_other']);
        $statements[2]['content_type'] = ['CONTENT_TYPE_OTHER'];
        $statements[2]['content_type_other'] = null;
        $statements[3]['content_type'] = ['CONTENT_TYPE_IMAGE'];
        $statements[3]['content_type_other'] = null;
        $statements[4]['content_type'] = ['CONTENT_TYPE_IMAGE', 'CONTENT_TYPE_OTHER'];
        $statements[4]['content_type_other'] = null;

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertCount(3, $response->json('errors'));
        $this->assertEquals('The content type other field is required.',
            $response->json('errors.statement_1.content_type_other.0'));
        $this->assertEquals('The content type other field is required.',
            $response->json('errors.statement_2.content_type_other.0'));
        $this->assertEquals('The content type other field is required.',
            $response->json('errors.statement_4.content_type_other.0'));
        $this->assertCount(10, Statement::all());
    }

    /**
     * @test
     */
    public function store_multiple_should_not_save_source_identity(): void
    {
        $user = $this->signInAsContributor();

        $this->assertCount(10, Statement::all());

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
            'source_type' => 'SOURCE_VOLUNTARY'
        ]);

        $create = 1;
        $sors = [];
        while ($create--) {
            $fields['puid'] = random_int(0, mt_getrandmax());
            $sors[] = $fields;
        }

        $response = $this->post(route('api.v1.statements.store'), ['statements' => $sors], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);

        $statement = Statement::where('puid', $response->json('statements.0.puid'))->first();

        $this->assertNotNull($statement->source_type);
        $this->assertNull($statement->source_identity);
    }

    /**
     * @test
     */
    public function store_multiple_with_different_content_types(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(2);

        $statements[0]['puid'] = 'testA';
        $statements[0]['content_type'] = ['CONTENT_TYPE_OTHER'];
        $statements[0]['content_type_other'] = 'content type other required field';
        $statements[1]['content_type'] = ['CONTENT_TYPE_IMAGE'];
        unset($statements[1]['content_type_other']);

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(12, Statement::all());

        $statement = Statement::where('puid', 'testA')->first()->fresh();
        $this->assertNotNull($statement->content_type);
        $this->assertEquals('content type other required field', $statement->content_type_other);
    }

    /**
     * @test
     */
    public function store_multiple_with_different_source_type(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(3);

        $statements[0]['puid'] = 'testSourceTypeA';
        $statements[0]['source_type'] = 'SOURCE_VOLUNTARY';
        $statements[0]['source_identity'] = 'should not be persisted';

        $statements[1]['puid'] = 'testSourceTypeB';
        $statements[1]['source_type'] = 'SOURCE_TRUSTED_FLAGGER';
        $statements[1]['source_identity'] = 'source identity required field';

        $statements[2]['puid'] = 'testSourceTypeC';
        $statements[2]['source_type'] = 'SOURCE_TRUSTED_FLAGGER';
        unset($statements[2]['source_identity']);

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(13, Statement::all());

        $statementA = Statement::where('puid', 'testSourceTypeA')->first()->fresh();
        $statementB = Statement::where('puid', 'testSourceTypeB')->first()->fresh();
        $this->assertNotNull($statementA->source_type);
        $this->assertNull($statementA->source_identity);
        $this->assertNotNull($statementB->source_type);
        $this->assertEquals('source identity required field', $statementB->source_identity);
    }

    /**
     * @test
     */
    public function store_multiple_with_different_decision_monetary(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(3);

        $statements[0]['puid'] = 'testDecisionMonetaryA';
        $statements[0]['decision_monetary'] = 'DECISION_MONETARY_OTHER';
        $statements[0]['decision_monetary_other'] = 'should be persisted';

        $statements[1]['puid'] = 'testDecisionMonetaryB';
        $statements[1]['decision_monetary'] = 'DECISION_MONETARY_SUSPENSION';
        $statements[1]['decision_monetary_other'] = 'should not be persisted';

        $statements[2]['puid'] = 'testDecisionMonetaryC';
        $statements[2]['decision_monetary'] = 'DECISION_MONETARY_SUSPENSION';
        unset($statements[2]['decision_monetary_other']);


        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(13, Statement::all());

        $statementA = Statement::where('puid', 'testDecisionMonetaryA')->first()->fresh();
        $statementB = Statement::where('puid', 'testDecisionMonetaryB')->first()->fresh();
        $statementC = Statement::where('puid', 'testDecisionMonetaryC')->first()->fresh();
        $this->assertNotNull($statementA->decision_monetary);
        $this->assertNotNull($statementA->decision_monetary_other);
        $this->assertEquals('should be persisted', $statementA->decision_monetary_other);

        $this->assertNull($statementB->decision_monetary_other);
        $this->assertNull($statementC->decision_monetary_other);
    }

    /**
     * @test
     */
    public function store_multiple_with_different_category_specifications(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(3);

        $statements[0]['puid'] = 'testA';
        $statements[0]['category_specification'] = ['KEYWORD_OTHER'];
        $statements[0]['category_specification_other'] = 'category specification other field value';

        $statements[1]['puid'] = 'testB';
        $statements[1]['category_specification'] = ['KEYWORD_BIOMETRIC_DATA_BREACH'];
        $statements[1]['category_specification_other'] = 'category specification other field value';

        $statements[2]['puid'] = 'testC';
        $statements[2]['category_specification'] = ['KEYWORD_BIOMETRIC_DATA_BREACH'];
        unset($statements[2]['category_specification_other']);

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(13, Statement::all());

        $statementA = Statement::where('puid', 'testA')->first()->fresh();
        $statementB = Statement::where('puid', 'testB')->first()->fresh();
        $statementC = Statement::where('puid', 'testC')->first()->fresh();
        $this->assertNotNull($statementA->category_specification);
        $this->assertEquals('category specification other field value', $statementA->category_specification_other);
        $this->assertNull($statementB->category_specification_other);
        $this->assertNull($statementC->category_specification_other);
    }

    /**
     * @test
     */
    public function store_multiple_with_different_decision_grounds(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(2);

        $statements[0]['puid'] = 'testDecisionGroundA';
        $statements[0]['decision_ground'] = 'DECISION_GROUND_ILLEGAL_CONTENT';
        $statements[0]['illegal_content_legal_ground'] = 'illegal_content_legal_ground should be persisted';
        $statements[0]['illegal_content_explanation'] = 'illegal_content_explanation should be persisted';
        unset($statements[0]['incompatible_content_ground']);
        unset($statements[0]['incompatible_content_explanation']);
        unset($statements[0]['incompatible_content_illegal']);

        $statements[1]['puid'] = 'testDecisionGroundB';
        $statements[1]['decision_ground'] = 'DECISION_GROUND_INCOMPATIBLE_CONTENT';
        $statements[1]['incompatible_content_ground'] = 'incompatible_content_ground should be persisted';
        $statements[1]['incompatible_content_explanation'] = 'incompatible_content_explanation should be persisted';
        $statements[1]['incompatible_content_illegal'] = 'Yes';
        unset($statements[1]['illegal_content_legal_ground']);
        unset($statements[1]['illegal_content_explanation']);

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(12, Statement::all());

        $statementA = Statement::where('puid', 'testDecisionGroundA')->first()->fresh();
        $statementB = Statement::where('puid', 'testDecisionGroundB')->first()->fresh();

        $this->assertNotNull($statementA->decision_ground);
        $this->assertEquals('illegal_content_legal_ground should be persisted',
            $statementA->illegal_content_legal_ground);
        $this->assertEquals('illegal_content_explanation should be persisted',
            $statementA->illegal_content_explanation);

        $this->assertNotNull($statementB->decision_ground);
        $this->assertEquals('incompatible_content_ground should be persisted',
            $statementB->incompatible_content_ground);
        $this->assertEquals('incompatible_content_explanation should be persisted',
            $statementB->incompatible_content_explanation);
        $this->assertEquals('Yes', $statementB->incompatible_content_illegal);
    }

    /**
     * @test
     */
    public function store_multiple_with_different_decision_visibility(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(2);

        $statements[0]['puid'] = 'testDecisionVisibilityA';
        $statements[0]['decision_visibility'] = ['DECISION_VISIBILITY_CONTENT_REMOVED', 'DECISION_VISIBILITY_OTHER'];
        $statements[0]['decision_visibility_other'] = 'decision_visibility_other should be persisted';

        $statements[1]['puid'] = 'testDecisionVisibilityB';
        $statements[1]['decision_visibility'] = ['DECISION_VISIBILITY_CONTENT_REMOVED'];
        unset($statements[1]['decision_visibility_other']);

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(12, Statement::all());

        $statementA = Statement::where('puid', 'testDecisionVisibilityA')->first()->fresh();
        $statementB = Statement::where('puid', 'testDecisionVisibilityB')->first()->fresh();

        $this->assertNotNull($statementA->decision_visibility);
        $this->assertEquals('decision_visibility_other should be persisted', $statementA->decision_visibility_other);

        $this->assertNotNull($statementB->decision_visibility);
        $this->assertNull($statementB->decision_visibility_other);
    }

    /**
     * @test
     */
    public function store_multiple_statements_with_different_attributes_should_be_persisted_and_visible(): void
    {
        $this->signInAsContributor();

        //$this->withoutExceptionHandling();

        $sors = [];

        //Create the light one and add it to the sors array
        $sors[] = [
            'decision_monetary' => 'DECISION_MONETARY_TERMINATION',
            'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'territorial_scope' => ['BE', 'DE', 'FR'],
            'source_type' => 'SOURCE_ARTICLE_16',
            'source_identity' => 'foo',
            'decision_facts' => 'decision and facts',
            'content_type' => ['CONTENT_TYPE_SYNTHETIC_MEDIA'],
            'automated_detection' => 'No',
            'automated_decision' => 'AUTOMATED_DECISION_PARTIALLY',
            'application_date' => '2023-05-18',
            'content_date' => '2023-05-18',
            'puid' => 'sorLight',
        ];

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);
        $fields['puid'] = "sorFull";
        $sors[] = $fields;


        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $sors
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(12, Statement::all());



        $statementA = Statement::where('puid', $response->json('statements.0.puid'))->first()->fresh();
        $statementB = Statement::where('puid', $response->json('statements.1.puid'))->first()->fresh();

        //Check that both can be displayed without errors
        $this->get(route('api.v1.statement.show', [$statementA]), [
            'Accept' => 'application/json'
        ])->assertStatus(Response::HTTP_OK);

        $this->get(route('api.v1.statement.show', [$statementB]), [
            'Accept' => 'application/json'
        ])->assertStatus(Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function store_multiple_with_category_addition(): void
    {
        $this->signInAsContributor();

        $statements = $this->createFullStatements(2);

        $statements[0]['puid'] = 'testCategoryAdditionA';
        $statements[0]['category_addition'] = [];
        $statements[1]['puid'] = 'testCategoryAdditionB';
        unset($statements[1]['category_addition']);

        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $statements
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(12, Statement::all());

        $statementA = Statement::where('puid', 'testCategoryAdditionA')->first()->fresh();
        $statementB = Statement::where('puid', 'testCategoryAdditionB')->first()->fresh();

        $this->assertEquals([], $statementA->category_addition);
        $this->assertEquals([], $statementB->category_addition);
    }


    /**
     * @test
     */
    public function it_should_reject_bad_puids(): void
    {
        $this->signInAsContributor();

        //$this->withoutExceptionHandling();

        $sors = [];

        //Create the light one and add it to the sors array
        $sors[] = [
            'decision_monetary' => 'DECISION_MONETARY_TERMINATION',
            'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category' => 'STATEMENT_CATEGORY_ANIMAL_WELFARE',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'territorial_scope' => ['BE', 'DE', 'FR'],
            'source_type' => 'SOURCE_ARTICLE_16',
            'source_identity' => 'foo',
            'decision_facts' => 'decision and facts',
            'content_type' => ['CONTENT_TYPE_SYNTHETIC_MEDIA'],
            'automated_detection' => 'No',
            'automated_decision' => 'AUTOMATED_DECISION_PARTIALLY',
            'application_date' => '2023-05-18',
            'content_date' => '2023-05-18',
            'puid' => 'very bad + â pu+id !',
        ];

        $fields = array_merge($this->required_fields, [
            'application_date' => '2023-12-20',
        ]);
        $fields['puid'] = "very bad + â pu+id !";
        $sors[] = $fields;


        $response = $this->post(route('api.v1.statements.store'), [
            "statements" => $sors
        ], [
            'Accept' => 'application/json'
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The puid format is invalid.', $response->json()['errors']['statement_0']['puid'][0]);
        $this->assertEquals('The puid format is invalid.', $response->json()['errors']['statement_1']['puid'][0]);
    }


}

