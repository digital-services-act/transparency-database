<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;


uses(RefreshDatabase::class);
uses(WithFaker::class);

it('should make api notice work', function () {
    $user = $this->signIn();
    $notice = Notice::create([
        'title' => 'Testing Title',
        'language' => 'en',
        'user_id' => $user->id
    ]);

    $response = $this->get(route('api.notice.show', [$notice]), [
        'Accept' => 'application/json'
    ]);
    $response->assertStatus(Response::HTTP_OK);
    $this->assertEquals($notice->title, $response->json('title'));
});

it('requires auth to create a notice', function () {
    // not signing in.
    $notice = Notice::create([
        'title' => 'Testing Title',
        'language' => 'en',
        'user_id' => 7
    ]);

    $response = $this->get(route('api.notice.show', [$notice]), [
        'Accept' => 'application/json'
    ]);
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('requires auth to store a notice', function () {
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
    ], [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('stores a notice', function () {
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
    ], [
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
});


it('should reject bad languages', function () {
    $this->signIn();
    $response = $this->post(route('api.notice.store'), [
        'title' => 'A Test Title',
        'language' => 'bad_language',
        'date_sent' => '2023-01-01 00:00:00',
        'date_enacted' => '2023-01-02 00:00:00',
        'date_abolished' => '2023-01-03 00:00:00',
        'source' => Notice::SOURCE_ARTICLE_16,
        'countries_list' => ['US', 'FR'],
    ], [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertEquals('The selected language is invalid.', $response->json('message'));
});

it('should reject bad countries', function () {
    $this->signIn();
    $response = $this->post(route('api.notice.store'), [
        'title' => 'A Test Title',
        'language' => 'fr',
        'date_sent' => '2023-01-01 00:00:00',
        'date_enacted' => '2023-01-02 00:00:00',
        'date_abolished' => '2023-01-03 00:00:00',
        'source' => Notice::SOURCE_ARTICLE_16,
        'countries_list' => ['US', 'INVALID COUNTRY'],
    ], [
        'Accept' => 'application/json'
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $this->assertEquals('The selected countries list is invalid.', $response->json('message'));
});

