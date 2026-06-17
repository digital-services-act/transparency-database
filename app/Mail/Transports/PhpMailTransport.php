<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class PhpMailTransport extends AbstractTransport
{
    private \Closure $mail;

    public function __construct(?callable $mail = null)
    {
        parent::__construct();

        $this->mail = \Closure::fromCallable($mail ?? 'mail');
    }

    public function __toString(): string
    {
        return 'php-mail://default';
    }

    protected function doSend(SentMessage $message): void
    {
        [$headers, $body] = $this->splitMessage($message->toString());

        $success = ($this->mail)(
            implode(', ', $this->stringifyAddresses($message->getEnvelope()->getRecipients())),
            $this->extractHeader($headers, 'Subject') ?? '',
            $body,
            $this->filterHeaders($headers)
        );

        if (! $success) {
            throw new TransportException('Unable to send email using PHP mail().');
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitMessage(string $message): array
    {
        $parts = preg_split("/\r\n\r\n|\n\n|\r\r/", $message, 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function extractHeader(string $headers, string $name): ?string
    {
        $lines = preg_split("/\r\n|\n|\r/", $headers) ?: [];
        $header = null;

        foreach ($lines as $line) {
            if (preg_match('/^\s/', $line)) {
                if ($header !== null) {
                    $header .= ' '.trim($line);
                }

                continue;
            }

            if ($header !== null) {
                break;
            }

            if (str_starts_with(strtolower($line), strtolower($name).':')) {
                $header = trim(substr($line, strlen($name) + 1));
            }
        }

        return $header;
    }

    private function filterHeaders(string $headers): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $headers) ?: [];
        $filtered = [];
        $skipContinuation = false;

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            if (preg_match('/^\s/', $line)) {
                if (! $skipContinuation) {
                    $filtered[] = $line;
                }

                continue;
            }

            $name = strtolower((string) strstr($line, ':', true));
            $skipContinuation = in_array($name, ['subject', 'to'], true);

            if (! $skipContinuation) {
                $filtered[] = $line;
            }
        }

        return implode(PHP_EOL, $filtered);
    }
}
