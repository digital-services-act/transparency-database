<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FeedbackPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('feedback.index'));
        $response->assertStatus(302); // Just check for redirect, as CAS handles the actual login
    }

    public function test_index_displays_feedback_support_content(): void
    {
        $this->signIn();

        $response = $this->get(route('feedback.index'));

        $response->assertStatus(200);
        $response->assertViewIs('feedback.feedback');
        $response->assertSee('Feedback and support');
        $response->assertSee('DSA Helpdesk');
        $response->assertSee('CNECT-DSA-HELPDESK@ec.europa.eu');
        $response->assertDontSee('<form', false);
        $response->assertDontSee('send-feedback-form');
    }

    public function test_send_route_has_been_removed(): void
    {
        $this->assertFalse(Route::has('feedback.send'));
    }
}
