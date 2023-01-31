<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use JMac\Testing\Traits\AdditionalAssertions;
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
     */
    public function store_saves_and_redirects()
    {
        $title = $this->faker->sentence(4);
        $language = 'en';

        $user = $this->signIn();

        $this->assertCount(0, Notice::all());

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
        $this->assertEquals($user->name, $notice->user->name);
        $this->assertEquals('2023-01-03 00:00:00', $notice->date_abolished);
        $this->assertInstanceOf(Carbon::class, $notice->date_abolished);

        $response->assertRedirect(route('notice.index'));
    }
}
