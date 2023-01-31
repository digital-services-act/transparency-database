<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\Feature\Http\Controllers\Api\NoticeAPIControllerTest;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\NoticeController
 */
class NoticeControllerTest extends TestCase
{
    use AdditionalAssertions, RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function index_displays_view()
    {
        $notices = Notice::factory()->count(3)->create();
        $response = $this->get(route('notice.index'));
        $response->assertOk();
        $response->assertViewIs('notice.index');
        $response->assertViewHas('notices');
    }

    /**
     * @test
     */
    public function index_does_not_auth()
    {
        // The cas is set to masquerade in testing mode.
        // So when we make a call to a cas middleware route we get logged in.
        // If we make a call to a non cas route nothing should happen.
        $u = auth()->user();
        $this->assertNull($u);
        $response = $this->get(route('notice.index'));
        $u = auth()->user();
        $this->assertNull($u);
    }


    /**
     * @test
     */
    public function create_displays_view()
    {
        $user = $this->signIn();
        $response = $this->get(route('notice.create'));
        $response->assertOk();
        $response->assertViewIs('notice.create');
    }

    /**
     * @test
     */
    public function create_must_be_authenticated()
    {
        // The cas is set to masquerade in testing mode.
        // So when we make a call to a cas middleware route we get logged in.
        // Thus before we make this call we are nobody
        $u = auth()->user();
        $this->assertNull($u);
        $response = $this->get(route('notice.create'));

        // After we made this call we are somebody
        $u = auth()->user();
        $this->assertNotNull($u);
    }


    /**
     * @test
     */
    public function show_displays_view()
    {
        $this->seed();
        $notice = Notice::factory()->create();
        $response = $this->get(route('notice.show', $notice));

        $response->assertOk();
        $response->assertViewIs('notice.show');
        $response->assertViewHas('notice');
    }


    /**
     * @test
     */
    public function store_uses_form_request_validation()
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\NoticeController::class,
            'store',
            \App\Http\Requests\NoticeStoreRequest::class
        );
    }

    /**
     * @test
     * @see NoticeAPIControllerTest
     */
    public function store_saves_and_redirects()
    {
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
    }
}
