<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use App\Models\Statement;
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
            'decision_visibility' => 'CONTENT_DISABLED',
            'decision_ground' => 'ILLEGAL_CONTENT',
            'category' => 'FRAUD',
            'platform_type' => 'SOCIAL_MEDIA',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'url' => 'https://www.test.com',
            'countries_list' => ['BE', 'DE', 'FR'],
            'source' => 'SOURCE_ARTICLE_16',
            'decision_facts' => 'decision and facts',
            'content_type' => 'VIDEO',
            'automated_detection' => 'No',
            'automated_decision' => 'No',
            'automated_takedown' => 'Yes',
            'user_id' => 1,
            'start_date' => '03-01-2023'
        ];
    }


    /**
     * @test
     */
    public function api_statement_show_works()
    {
        $this->signInAsAdmin();
        $this->statement = Statement::create($this->required_fields);
        $response = $this->get(route('api.v1.statement.show', [$this->statement]), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($this->statement->decision_ground, $response->json('decision_ground'));
    }

    /**
     * @test
     */
    public function api_statement_show_requires_auth()
    {
        $this->statement = Statement::create($this->required_fields);
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
        $this->seed();

        // Not signing in.
        $this->assertCount(200, Statement::all());
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
        $this->seed();
        $user = $this->signInAsAdmin();
        $this->assertCount(200, Statement::all());
        $fields = array_merge($this->required_fields, [
            'start_date' => '2023-01-03 00:00:00',
            'end_date' => '2023-01-13 00:00:00',
        ]);
        $response = $this->post(route('api.v1.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(201, Statement::all());
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
        $this->seed();
        $user = $this->signInAsAdmin();
        $this->assertCount(200, Statement::all());
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
        $this->assertCount(201, Statement::all());
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
        $this->signInAsAdmin();
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
        $this->signInAsAdmin();
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
        $this->signInAsAdmin();
        $extra_fields = [
            'decision_ground' => 'INCOMPATIBLE_CONTENT',
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
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
