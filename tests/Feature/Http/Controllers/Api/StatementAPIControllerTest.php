<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Statement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
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
            'decision_taken' => 'DECISION_ALL',
            'decision_ground' => 'ILLEGAL_CONTENT',
            'illegal_content_legal_ground' => 'foo',
            'illegal_content_explanation' => 'bar',
            'source' => 'SOURCE_ARTICLE_16',
            'automated_detection' => 'No',
            'user_id' => 1,
        ];


    }

    /**
     * @test
     */
    public function api_statement_show_works()
    {
        $this->signIn(User::whereId(1)->first());
        $this->statement = Statement::create($this->required_fields);
        $response = $this->get(route('api.statement.show', [$this->statement]), [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($this->statement->user_id, $response->json('user_id'));
    }

    /**
     * @test
     */
    public function api_statement_show_requires_auth()
    {
        $this->statement = Statement::create($this->required_fields);

        $response = $this->get(route('api.statement.show', [$this->statement]), [
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

        $title = $this->faker->sentence(4);
        $language = 'en';

        // Not signing in.

        $this->assertCount(200, Statement::all());
        $response = $this->post(route('api.statement.store'), $this->required_fields, [
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

        $user = $this->signIn();

        $this->assertCount(200, Statement::all());

        $fields = array_merge($this->required_fields, [
            'date_abolished' => '2023-01-03 00:00:00',
        ]);
        $response = $this->post(route('api.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(201, Statement::all());
        $statement = Statement::find($response->json('statement')['id']);
        $this->assertNotNull($statement);
        $this->assertEquals('API', $statement->method);
        $this->assertEquals($user->id, $statement->user->id);
        $this->assertEquals('2023-01-03 00:00:00', $statement->date_abolished);
        $this->assertInstanceOf(Carbon::class, $statement->date_abolished);
    }


    /**
     * @test
     */
    public function request_rejects_bad_countries()
    {
        $this->signIn();

        $fields = array_merge($this->required_fields, [
            'countries_list' => ['XY', 'ZZ'],
        ]);
        $response = $this->post(route('api.statement.store'), $fields, [
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
        $this->signIn();

        $extra_fields = [
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
        ];

        $fields = array_merge($this->required_fields, $extra_fields);
        $response = $this->post(route('api.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $statement = Statement::find($response->json('statement')['id']);
        $this->assertNull($statement->incompatible_content_ground);
        $this->assertNull($statement->incompatible_content_explanation);

    }


    /**
     * @test
     */
    public function store_does_not_save_optional_fields_non_related_to_incompatible_content()
    {
        $this->signIn();

        $extra_fields = [
            'decision_ground' => 'INCOMPATIBLE_CONTENT',
            'incompatible_content_ground' => 'foobar',
            'incompatible_content_explanation' => 'foobar2',
        ];

        $fields = array_merge($this->required_fields, $extra_fields);
        $response = $this->post(route('api.statement.store'), $fields, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $statement = Statement::find($response->json('statement')['id']);
        $this->assertNull($statement->illegal_content_legal_ground);
        $this->assertNull($statement->illegal_content_explanation);

    }




}
