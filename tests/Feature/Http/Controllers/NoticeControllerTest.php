<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;

use function PHPUnit\Framework\assertCount;


uses(AdditionalAssertions::class);
uses(RefreshDatabase::class);
uses(WithFaker::class);

it('should display view', function(){
    $notices = Notice::factory()->count(3)->create();
    $response = $this->get(route('notice.index'));
    $response->assertOk();
    $response->assertViewIs('notice.index');
    $response->assertViewHas('notices');
});


it('does not auth on index', function(){
    $u = auth()->user();
    $this->assertNull($u);
    $response = $this->get(route('notice.index'));
    $u = auth()->user();
    $this->assertNull($u);
});

it('requires authentication to see the create page', function(){
    // The cas is set to masquerade in testing mode.
    // So when we make a call to a cas middleware route we get logged in.
    // Thus before we make this call we are nobody
    $u = auth()->user();
    $this->assertNull($u);

    $response = $this->get(route('notice.create'));

    // After we made this call we are somebody
    $u = auth()->user();
    $this->assertNotNull($u);
});


it('should display the create view', function () {
    $user = $this->signIn();
    $response = $this->get(route('notice.create'));
    $response->assertOk();
    $response->assertViewIs('notice.create');
});


it('requires authentication to create a notice', function () {
    // The cas is set to masquerade in testing mode.
    // So when we make a call to a cas middleware route we get logged in.
    // Thus before we make this call we are nobody
    $u = auth()->user();
    $this->assertNull($u);
    $response = $this->get(route('notice.create'));

    // After we made this call we are somebody
    $u = auth()->user();
    $this->assertNotNull($u);
});

it('should display show view', function () {
    $this->seed();
    $notice = Notice::factory()->create();
    $response = $this->get(route('notice.show', $notice));

    $response->assertOk();
    $response->assertViewIs('notice.show');
    $response->assertViewHas('notice');
});


it('should use form request validation', function () {
    $this->assertActionUsesFormRequest(
        \App\Http\Controllers\NoticeController::class,
        'store',
        \App\Http\Requests\NoticeStoreRequest::class
    );
});

it('should save and redirect', function () {
    // This is a basic test that the normal controller is working.
    // For more advanced testing on the request and such, see the API testing.
    $title = $this->faker->sentence(4);
    $language = 'en';

    $user = $this->signIn();

    $this->assertCount(0, Notice::all());

    // When making notices via the FORM
    // The dates come in as d-m-Y from the ECL datepicker.
    $response = $this->post(route('notice.store'), [
        'title' => $title,
        'language' => $language,
        'date_sent' => '01-01-2023',
        'date_enacted' => '02-01-2023',
        'date_abolished' => '03-01-2023',
        'source' => Notice::SOURCE_ARTICLE_16
    ]);


    $this->assertCount(1, Notice::all());
    $notice = Notice::latest()->first();
    $this->assertNotNull($notice);
    $this->assertEquals('FORM', $notice->method);
    $this->assertEquals($user->id, $notice->user->id);
    $this->assertEquals('2023-01-03 00:00:00', $notice->date_abolished);
    $this->assertInstanceOf(Carbon::class, $notice->date_abolished);

    $response->assertRedirect(route('notice.index'));
});
