<?php

namespace Tests\Feature\Http\Controllers\Api;

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

    /**
     * @test
     */
    public function api_statement_show_works()
    {
        $user = $this->signIn();
        $statement = Statement::create([
            'title' => 'Testing Title',
            'language' => 'en',
            'user_id' => $user->id
        ]);

        $response = $this->get(route('api.statement.show', [$statement]),[
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($statement->title, $response->json('title'));
    }

    /**
     * @test
     */
    public function api_statement_show_requires_auth()
    {
        // not signing in.

        $statement = Statement::create([
            'title' => 'Testing Title',
            'language' => 'en',
            'user_id' => 7
        ]);

        $response = $this->get(route('api.statement.show', [$statement]),[
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

        $this->assertCount(10, Statement::all());
        $response = $this->post(route('api.statement.store'), [
            'title' => $title,
            'language' => $language,
            'date_sent' => '2023-01-01 00:00:00',
            'date_enacted' => '2023-01-02 00:00:00',
            'date_abolished' => '2023-01-03 00:00:00',
            'source' => Statement::SOURCE_ARTICLE_16
        ],[
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

        $title = $this->faker->sentence(4);
        $language = 'fr';

        $user = $this->signIn();

        $this->assertCount(10, Statement::all());
        $response = $this->post(route('api.statement.store'), [
            'title' => $title,
            'language' => $language,
            'date_sent' => '2023-01-01 00:00:00',
            'date_enacted' => '2023-01-02 00:00:00',
            'date_abolished' => '2023-01-03 00:00:00',
            'source' => Statement::SOURCE_ARTICLE_16,
            'countries_list' => ['US', 'FR'],
        ],[
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(11, Statement::all());
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
    public function request_rejects_bad_languages()
    {
        $this->signIn();
        $response = $this->post(route('api.statement.store'), [
            'title' => 'A Test Title',
            'language' => 'bad_language',
            'date_sent' => '2023-01-01 00:00:00',
            'date_enacted' => '2023-01-02 00:00:00',
            'date_abolished' => '2023-01-03 00:00:00',
            'source' => Statement::SOURCE_ARTICLE_16,
            'countries_list' => ['US', 'FR'],
        ],[
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The selected language is invalid.', $response->json('message'));
    }

    /**
     * @test
     */
    public function request_rejects_bad_countries()
    {
        $this->signIn();
        $response = $this->post(route('api.statement.store'), [
            'title' => 'A Test Title',
            'language' => 'fr',
            'date_sent' => '2023-01-01 00:00:00',
            'date_enacted' => '2023-01-02 00:00:00',
            'date_abolished' => '2023-01-03 00:00:00',
            'source' => Statement::SOURCE_ARTICLE_16,
            'countries_list' => ['US', 'INVALID COUNTRY'],
        ],[
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The selected countries list is invalid.', $response->json('message'));
    }
}
