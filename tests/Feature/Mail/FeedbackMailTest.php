<?php

namespace Tests\Feature\Mail;

use App\Mail\FeedbackMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\CreatesApplication;

class FeedbackMailTest extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @test */
    public function it_constructs_with_feedback_message()
    {
        $feedback = 'Test feedback message';
        $mail = new FeedbackMail($feedback);

        $this->assertEquals($feedback, $mail->feedback);
    }

    /** @test */
    public function it_has_correct_envelope()
    {
        $mail = new FeedbackMail('Test feedback');
        $envelope = $mail->envelope();

        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals('Feedback Received', $envelope->subject);
    }

    /** @test */
    public function it_has_correct_content()
    {
        $mail = new FeedbackMail('Test feedback');
        $content = $mail->content();

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('feedback.mail', $content->markdown);
    }

    /** @test */
    public function it_has_no_attachments()
    {
        $mail = new FeedbackMail('Test feedback');
        $attachments = $mail->attachments();

        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    /** @test */
    public function it_renders_markdown_view()
    {
        // Create a mock user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')
            ->with('name')
            ->andReturn('Test User');
        $user->shouldReceive('getAttribute')
            ->with('email')
            ->andReturn('test@example.com');

        // Mock the auth facade
        Auth::shouldReceive('user')
            ->andReturn($user)
            ->twice();

        $feedback = 'Test feedback message';
        $mail = new FeedbackMail($feedback);

        $rendered = $mail->render();

        $this->assertStringContainsString($feedback, $rendered);
        $this->assertStringContainsString('Test User', $rendered);
        $this->assertStringContainsString('test@example.com', $rendered);
    }

    /** @test */
    public function it_is_queueable()
    {
        $mail = new FeedbackMail('Test feedback');

        $this->assertTrue(
            in_array('Illuminate\Bus\Queueable', class_uses_recursive($mail)),
            'FeedbackMail should use Queueable trait'
        );
    }

    /** @test */
    public function it_serializes_models()
    {
        $mail = new FeedbackMail('Test feedback');

        $this->assertTrue(
            in_array('Illuminate\Queue\SerializesModels', class_uses_recursive($mail)),
            'FeedbackMail should use SerializesModels trait'
        );
    }
}
