<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Statement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;

use function PHPUnit\Framework\assertCount;


uses(AdditionalAssertions::class);
uses(RefreshDatabase::class);
uses(WithFaker::class);

it('should display view', function(){
    $statements = Statement::factory()->count(3)->create();
    $response = $this->get(route('statement.index'));
    $response->assertOk();
    $response->assertViewIs('statement.index');
    $response->assertViewHas('statements');
});


it('does not auth on index', function(){
    $u = auth()->user();
    $this->assertNull($u);
    $response = $this->get(route('statement.index'));
    $u = auth()->user();
    $this->assertNull($u);
});

it('requires authentication to see the create page', function(){
    // The cas is set to masquerade in testing mode.
    // So when we make a call to a cas middleware route we get logged in.
    // Thus before we make this call we are nobody
    $u = auth()->user();
    $this->assertNull($u);

    $response = $this->get(route('statement.create'));

    // After we made this call we are somebody
    $u = auth()->user();
    $this->assertNotNull($u);
});


it('should display the create view', function () {
    $user = $this->signIn();
    $response = $this->get(route('statement.create'));
    $response->assertOk();
    $response->assertViewIs('statement.create');
});


it('requires authentication to create a statement', function () {
    // The cas is set to masquerade in testing mode.
    // So when we make a call to a cas middleware route we get logged in.
    // Thus before we make this call we are nobody
    $u = auth()->user();
    $this->assertNull($u);
    $response = $this->get(route('statement.create'));

    // After we made this call we are somebody
    $u = auth()->user();
    $this->assertNotNull($u);
});

it('should display show view', function () {
    $this->seed();
    $statement = Statement::factory()->create();
    $response = $this->get(route('statement.show', $statement));

    $response->assertOk();
    $response->assertViewIs('statement.show');
    $response->assertViewHas('statement');
});


it('should use form request validation', function () {
    $this->assertActionUsesFormRequest(
        \App\Http\Controllers\StatementController::class,
        'store',
        \App\Http\Requests\StatementStoreRequest::class
    );
});

it('should save and redirect', function () {
    // This is a basic test that the normal controller is working.
    // For more advanced testing on the request and such, see the API testing.
    $title = $this->faker->sentence(4);
    $language = 'en';

    $user = $this->signIn();

    $this->assertCount(0, Statement::all());

    // When making statements via the FORM
    // The dates come in as d-m-Y from the ECL datepicker.
    $response = $this->post(route('statement.store'), [
        'title' => $title,
        'language' => $language,
        'date_sent' => '01-01-2023',
        'date_enacted' => '02-01-2023',
        'date_abolished' => '03-01-2023',
        'source' => Statement::SOURCE_ARTICLE_16
    ]);


    $this->assertCount(1, Statement::all());
    $statement = Statement::latest()->first();
    $this->assertNotNull($statement);
    $this->assertEquals('FORM', $statement->method);
    $this->assertEquals($user->id, $statement->user->id);
    $this->assertEquals('2023-01-03 00:00:00', $statement->date_abolished);
    $this->assertInstanceOf(Carbon::class, $statement->date_abolished);

    $response->assertRedirect(route('statement.index'));
});
