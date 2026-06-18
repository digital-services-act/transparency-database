<?php

namespace Tests\Feature\Mail;

use App\Mail\Transports\PhpMailTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Email;

class PhpMailTransportTest extends TestCase
{
    public function test_it_sends_rendered_message_through_php_mail(): void
    {
        $mailArguments = [];
        $transport = new PhpMailTransport(static function (...$arguments) use (&$mailArguments): bool {
            $mailArguments = $arguments;

            return true;
        });

        $transport->send(
            (new Email)
                ->from('sender@example.com')
                ->to('recipient@example.com')
                ->subject('Native mail test')
                ->html('<p>Hello from PHP mail.</p>')
        );

        $this->assertSame('recipient@example.com', $mailArguments[0]);
        $this->assertSame('Native mail test', $mailArguments[1]);
        $this->assertStringContainsString('<p>Hello from PHP mail.</p>', $mailArguments[2]);
        $this->assertStringContainsString('From: sender@example.com', $mailArguments[3]);
        $this->assertDoesNotMatchRegularExpression('/(^|\R)To:/', $mailArguments[3]);
        $this->assertDoesNotMatchRegularExpression('/(^|\R)Subject:/', $mailArguments[3]);
    }

    public function test_it_handles_folded_subject_and_custom_headers(): void
    {
        $mailArguments = [];
        $transport = new PhpMailTransport(static function (...$arguments) use (&$mailArguments): bool {
            $mailArguments = $arguments;

            return true;
        });
        $subject = str_repeat('Long subject words ', 20);
        $customHeader = str_repeat('metadata words ', 20);
        $email = (new Email)
            ->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject($subject)
            ->text('Body');
        $email->getHeaders()->addTextHeader('X-Long', $customHeader);

        $transport->send($email);

        $this->assertSame(trim($subject), $mailArguments[1]);
        $this->assertStringContainsString('X-Long: metadata words', $mailArguments[3]);
        $this->assertMatchesRegularExpression('/X-Long:.*\R /s', $mailArguments[3]);
        $this->assertDoesNotMatchRegularExpression('/(^|\R)Subject:/', $mailArguments[3]);
        $this->assertSame('php-mail://default', (string) $transport);
    }

    public function test_it_throws_when_php_mail_fails(): void
    {
        $transport = new PhpMailTransport(static fn (): bool => false);

        $this->expectException(TransportException::class);

        $transport->send(
            (new Email)
                ->from('sender@example.com')
                ->to('recipient@example.com')
                ->subject('Native mail test')
                ->text('Hello from PHP mail.')
        );
    }
}
