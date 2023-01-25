<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $response = $this->get(route('notice.create'));

        $response->assertOk();
        $response->assertViewIs('notice.create');
    }


    /**
     * @test
     */
    public function show_displays_view()
    {
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
        $language = $this->faker->word;

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('notice.store'), [
            'title' => $title,
            'language' => $language,
            'user_id' => $user->id,
        ]);

        $notices = Notice::query()
            ->where('title', $title)
            ->where('language', $language)
            ->where('user_id', $user->id)
            ->get();
        $this->assertCount(1, $notices);
        $notice = $notices->first();

        $response->assertRedirect(route('notice.index'));
    }
}
