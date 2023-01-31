<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;


class NoticeAPIControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function gets_a_notice()
    {
        $user = $this->signIn();
        $notice = Notice::create([
            'title' => 'Testing Title',
            'language' => 'en',
            'user_id' => $user->id
        ]);

        $response = $this->get(route('api.notice.show', [$notice]),[
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertEquals($notice->title, $response->json('title'));
    }

    /**
     * @test
     */
    public function notice_show_requires_auth()
    {
        $notice = Notice::create([
            'title' => 'Testing Title',
            'language' => 'en',
            'user_id' => 7
        ]);

        $response = $this->get(route('api.notice.show', [$notice]),[
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @test
     */
    public function must_be_authenticated()
    {
        $this->seed();

        $title = $this->faker->sentence(4);
        $language = 'en';

        // Not signing in.

        $this->assertCount(10, Notice::all());
        $response = $this->post(route('api.notice.store'), [
            'title' => $title,
            'language' => $language,
            'date_sent' => '2023-01-01 00:00:00',
            'date_enacted' => '2023-01-02 00:00:00',
            'date_abolished' => '2023-01-03 00:00:00',
            'source' => Notice::SOURCE_ARTICLE_16
        ],[
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        
    }

    /**
     * @test
     */
    public function store_saves()
    {
        $this->seed();

        $title = $this->faker->sentence(4);
        $language = 'fr';

        $user = $this->signIn();

        $this->assertCount(10, Notice::all());
        $response = $this->post(route('api.notice.store'), [
            'title' => $title,
            'language' => $language,
            'date_sent' => '2023-01-01 00:00:00',
            'date_enacted' => '2023-01-02 00:00:00',
            'date_abolished' => '2023-01-03 00:00:00',
            'source' => Notice::SOURCE_ARTICLE_16,
            'countries_list' => ['US', 'FR'],
        ],[
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(11, Notice::all());
        $notice = Notice::find($response->json('notice')['id']);
        $this->assertNotNull($notice);
        $this->assertEquals('API', $notice->method);
        $this->assertEquals($user->id, $notice->user->id);
        $this->assertEquals('2023-01-03 00:00:00', $notice->date_abolished);
        $this->assertInstanceOf(Carbon::class, $notice->date_abolished);
    }


    /**
     * @test
     */
    public function it_rejects_bad_languages()
    {
        $this->signIn();
        $response = $this->post(route('api.notice.store'), [
            'title' => 'A Test Title',
            'language' => 'bad_language',
            'date_sent' => '2023-01-01 00:00:00',
            'date_enacted' => '2023-01-02 00:00:00',
            'date_abolished' => '2023-01-03 00:00:00',
            'source' => Notice::SOURCE_ARTICLE_16,
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
    public function it_rejects_bad_countries()
    {
        $this->signIn();
        $response = $this->post(route('api.notice.store'), [
            'title' => 'A Test Title',
            'language' => 'fr',
            'date_sent' => '2023-01-01 00:00:00',
            'date_enacted' => '2023-01-02 00:00:00',
            'date_abolished' => '2023-01-03 00:00:00',
            'source' => Notice::SOURCE_ARTICLE_16,
            'countries_list' => ['US', 'INVALID COUNTRY'],
        ],[
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals('The selected countries list is invalid.', $response->json('message'));
    }

}
