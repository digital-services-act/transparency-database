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
    public function must_be_authenticated()
    {
        $this->seed();

        $title = $this->faker->sentence(4);
        $language = 'en';

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
        $language = 'en';

        $user = User::factory()->create();
        $this->actingAs($user);

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
        $response->assertStatus(Response::HTTP_OK);

        $this->assertCount(11, Notice::all());
        $notice = Notice::find($response->json('notice')['id']);
        $this->assertNotNull($notice);
        $this->assertEquals('API', $notice->method);
        $this->assertEquals($user->name, $notice->user->name);
        $this->assertEquals('2023-01-03 00:00:00', $notice->date_abolished);
        $this->assertInstanceOf(Carbon::class, $notice->date_abolished);

    }
}
