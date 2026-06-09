<?php

namespace Tests\Feature\Http\Controllers;

use App\Mail\FeedbackMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['dsa.FEEDBACK_MAIL' => 'feedback@example.com']);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('feedback.index'));
        $response->assertStatus(302); // Just check for redirect, as CAS handles the actual login
    }

    public function test_index_displays_feedback_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('feedback.index'));

        $response->assertStatus(200);
        $response->assertViewIs('feedback.feedback');
    }

    public function test_send_requires_authentication(): void
    {
        $response = $this->post(route('feedback.send'), [
            'feedback' => 'Test feedback message',
        ]);

        $response->assertStatus(302); // Just check for redirect, as CAS handles the actual login
    }

    public function test_send_validates_feedback_content(): void
    {
        $user = User::factory()->create();

        // Test empty feedback
        $response = $this->actingAs($user)
            ->post(route('feedback.send'), [
                'feedback' => '',
            ]);
        $response->assertSessionHasErrors(['feedback']);

        // Test feedback too long (over 500 chars)
        $longFeedback = str_repeat('a', 501);
        $response = $this->actingAs($user)
            ->post(route('feedback.send'), [
                'feedback' => $longFeedback,
            ]);
        $response->assertSessionHasErrors(['feedback']);

        // Test non-string feedback
        $response = $this->actingAs($user)
            ->post(route('feedback.send'), [
                'feedback' => ['not', 'a', 'string'],
            ]);
        $response->assertSessionHasErrors(['feedback']);
    }

    public function test_send_purifies_html_content(): void
    {
        $user = User::factory()->create();
        Mail::fake();

        $feedbackWithHtml = '<p>Test feedback</p><script>alert("xss")</script>';

        $response = $this->actingAs($user)
            ->post(route('feedback.send'), [
                'feedback' => $feedbackWithHtml,
            ]);

        Mail::assertSent(FeedbackMail::class, function ($mail) {
            // The script tag should be removed, but p tag should remain
            return str_contains($mail->feedback, '<p>Test feedback</p>')
                && ! str_contains($mail->feedback, '<script>');
        });
    }

    public function test_send_delivers_email(): void
    {
        $user = User::factory()->create();
        Mail::fake();

        $feedbackContent = 'Test feedback message';

        $response = $this->actingAs($user)
            ->post(route('feedback.send'), [
                'feedback' => $feedbackContent,
            ]);

        Mail::assertSent(FeedbackMail::class, function ($mail) use ($feedbackContent) {
            return $mail->feedback === $feedbackContent
                && $mail->envelope()->subject === 'Feedback Received';
        });

        Mail::assertSent(FeedbackMail::class, function ($mail) {
            return $mail->hasTo(config('dsa.FEEDBACK_MAIL'));
        });
    }

    public function test_send_redirects_with_success_message(): void
    {
        $user = User::factory()->create();
        Mail::fake();

        $response = $this->actingAs($user)
            ->post(route('feedback.send'), [
                'feedback' => 'Test feedback message',
            ]);

        $response->assertRedirect(route('feedback.index'));
        $response->assertSessionHas('success', 'Your feedback has been successfully sent.');
    }
}
