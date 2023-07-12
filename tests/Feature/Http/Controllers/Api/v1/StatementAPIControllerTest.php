<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Platform;
use App\Models\Statement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;



class StatementAPIControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    private array $required_fields;
    private Statement $statement;


    protected function setUp(): void
    {
        parent::setUp();

        $this->required_fields = [
            'decision_visibility' => 'DECISION_VISIBILITY_CONTENT_DISABLED',
            'decision_ground' => 'DECISION_GROUND_ILLEGAL_CONTENT',
            'category' => 'STATEMENT_CATEGORY_FRAUD',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'url' => 'https://www.test.com',
            'countries_list' => ['BE', 'DE', 'FR'],
            'source_type' => 'SOURCE_ARTICLE_16',
            'source' => 'foo',
            'decision_facts' => 'decision and facts',
            'content_type' => 'CONTENT_TYPE_VIDEO',
            'automated_detection' => 'No',
            'automated_decision' => 'No',
            'start_date' => '03-01-2023'
        ];
    }


    /**
     * @test
     */
    public function api_statement_show_works()
    {
        $this->setUpFullySeededDatabase();
        $admin = $this->signInAsAdmin();
        $attributes = $this->required_fields;
        $attributes['user_id'] = $admin->id;
        $attributes['platform_id'] = $admin->platform_id;
        $this->statement = Statement::create($attributes);

        $response = $this->get(route('api.v1.statement.show', [$this->statement]), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($this->statement->decision_ground, $response->json('decision_ground'));
        $this->assertEquals($this->statement->uuid, $response->json('uuid'));
    }

    /**
     * @test
     */
    public function api_statement_show_requires_auth()
    {
        $this->setUpFullySeededDatabase();
        $attributes = $this->required_fields;
        $attributes['user_id'] = User::all()->random()->first()->id;
        $attributes['platform_id'] = Platform::all()->random()->first()->id;
        $this->statement = Statement::create($attributes);
        $response = $this->get(route('api.v1.statement.show', [$this->statement]), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_statement_store_requires_auth()
    {
        $this->setUpFullySeededDatabase();
        // Not signing in.
        $this->assertCount(10, Statement::all());
        $response = $this->post(route('api.v1.statement.store'), $this->required_fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function api_statement_store_works()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'start_date' => '2023-01-03 00:00:00',
            'end_date' => '2023-01-13 00:00:00',
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertEquals('2023-01-03 00:00:00', $statement->start_date);
        $this->assertEquals('2023-01-13 00:00:00', $statement->end_date);
        $this->assertInstanceOf(Carbon::class, $statement->start_date);
        $this->assertInstanceOf(Carbon::class, $statement->end_date);
    }

    /**
     * @test
     */
    public function api_statement_json_store_works()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $this->assertCount(10, Statement::all());
        $fields = array_merge($this->required_fields, [
            'start_date' => '2023-01-03 00:00:00',
            'end_date' => '2023-01-13 00:00:00',
        ]);
        $object = new \stdClass();
        foreach ($fields as $key => $value) {
            $object->$key = $value;
        }
        $json = json_encode($object);
        $response = $this->call(
            'POST',
            route('api.v1.statement.store'),
            [],
            [],
            [],
            $headers = [
                'HTTP_CONTENT_LENGTH' => mb_strlen($json, '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json'
            ],
            $json
        );

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(11, Statement::all());
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertEquals('2023-01-03 00:00:00', $statement->start_date);
        $this->assertInstanceOf(Carbon::class, $statement->start_date);
        $this->assertEquals('2023-01-13 00:00:00', $statement->end_date);
        $this->assertInstanceOf(Carbon::class, $statement->end_date);
    }

    /**
     * @test
     */
    public function request_rejects_bad_countries()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $fields = array_merge($this->required_fields, [
            'countries_list' => ['XY', 'ZZ'],
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The selected countries list is invalid.', $response->json('message'));
    }

    /**
     * @test
     */
    public function store_does_not_save_optional_fields_non_related_to_illegal_content()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNull($statement->incompatible_content_ground);
        $this->assertNull($statement->incompatible_content_explanation);
    }


    /**
     * @test
     */
    public function store_does_not_save_optional_fields_non_related_to_incompatible_content()
    {
        $this->setUpFullySeededDatabase();
        $user = $this->signInAsAdmin();

        $extra_fields = [
            'decision_ground' => 'DECISION_GROUND_INCOMPATIBLE_CONTENT',
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
            'incompatible_content_illegal' => 'No',
        ];
        $fields = array_merge($this->required_fields, $extra_fields);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $statement = Statement::where('uuid', $response->json('uuid'))->first();
        $this->assertNull($statement->illegal_content_legal_ground);
        $this->assertNull($statement->illegal_content_explanation);
    }
}
