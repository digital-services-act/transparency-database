<?php

namespace Tests\Feature\Services;

use App\Services\StatementValidationFailureLogMonitor;
use PHPUnit\Framework\TestCase;

class StatementValidationFailureLogMonitorTest extends TestCase
{
    public function test_it_groups_single_endpoint_validation_mistakes_by_platform(): void
    {
        $monitor = new StatementValidationFailureLogMonitor;

        $ingested = $monitor->ingest($this->logLine('Statement Store Request Validation Failure', [
            'errors' => [
                'Illuminate\\Support\\MessageBag' => [
                    'incompatible_content_explanation' => [
                        'The incompatible content explanation field is required when decision ground is incompatible content.',
                    ],
                ],
            ],
            'platform' => 'SIA "JOOM"',
        ]));

        $summary = $monitor->summary();

        $this->assertTrue($ingested);
        $this->assertSame(1, $summary['failures']);
        $this->assertSame(1, $summary['mistakes']);
        $this->assertSame(1, $summary['endpoints']['single']);
        $this->assertSame(0, $summary['endpoints']['multiple']);
        $this->assertSame('SIA "JOOM"', $summary['platforms'][0]['platform']);
        $this->assertSame(1, $summary['platforms'][0]['attempts']);
        $this->assertSame([
            'incompatible_content_explanation: The incompatible content explanation field is required when decision ground is incompatible content.' => 1,
        ], $summary['platforms'][0]['mistakes']);
    }

    public function test_it_groups_multiple_statement_errors_without_statement_indexes(): void
    {
        $monitor = new StatementValidationFailureLogMonitor;

        $monitor->ingest($this->logLine('Statement Multiple Store Request Validation Failure', [
            'errors' => [
                'statement_0' => [
                    'category' => ['The selected category is invalid.'],
                ],
                'statement_1' => [
                    'category' => ['The selected category is invalid.'],
                    'content_date' => ['The content date field is required.'],
                ],
            ],
            'platform' => 'TikTok',
        ]));

        $summary = $monitor->summary();

        $this->assertSame(1, $summary['failures']);
        $this->assertSame(3, $summary['mistakes']);
        $this->assertSame(0, $summary['endpoints']['single']);
        $this->assertSame(1, $summary['endpoints']['multiple']);
        $this->assertSame([
            'category: The selected category is invalid.' => 2,
            'content_date: The content date field is required.' => 1,
        ], $summary['platforms'][0]['mistakes']);
    }

    public function test_it_ranks_platforms_by_failed_attempts(): void
    {
        $monitor = new StatementValidationFailureLogMonitor;

        $monitor->ingest($this->logLine('Statement Store Request Validation Failure', [
            'errors' => ['field' => ['First issue.']],
            'platform' => 'Joom',
        ]));
        $monitor->ingest($this->logLine('Statement Store Request Validation Failure', [
            'errors' => ['field' => ['Second issue.']],
            'platform' => 'TikTok',
        ]));
        $monitor->ingest($this->logLine('Statement Multiple Store Request Validation Failure', [
            'errors' => ['statement_0' => ['field' => ['Third issue.']]],
            'platform' => 'TikTok',
        ]));

        $topPlatforms = $monitor->topPlatforms();

        $this->assertSame('TikTok', $topPlatforms[0]['platform']);
        $this->assertSame(2, $topPlatforms[0]['attempts']);
        $this->assertSame('Joom', $topPlatforms[1]['platform']);
        $this->assertSame(1, $topPlatforms[1]['attempts']);
    }

    public function test_it_ignores_unrelated_or_malformed_lines(): void
    {
        $monitor = new StatementValidationFailureLogMonitor;

        $this->assertFalse($monitor->ingest('[2026-06-24 12:00:00] production.INFO: Something else {"platform":"TikTok"}'));
        $this->assertFalse($monitor->ingest('[2026-06-24 12:00:00] production.INFO: Statement Store Request Validation Failure not-json'));

        $this->assertSame(0, $monitor->summary()['failures']);
    }

    public function test_it_parses_clever_logs_with_ansi_reset_suffix(): void
    {
        $monitor = new StatementValidationFailureLogMonitor;

        $monitor->ingest($this->logLine('Statement Store Request Validation Failure', [
            'errors' => [
                'Illuminate\\Support\\MessageBag' => [
                    'incompatible_content_explanation' => [
                        'The incompatible content explanation field is required when decision ground is incompatible content.',
                    ],
                ],
            ],
            'platform' => 'SIA "JOOM"',
        ])."\033[0m");

        $summary = $monitor->summary();

        $this->assertSame(1, $summary['failures']);
        $this->assertSame('SIA "JOOM"', $summary['platforms'][0]['platform']);
    }

    public function test_it_parses_json_with_braces_inside_string_values(): void
    {
        $monitor = new StatementValidationFailureLogMonitor;

        $monitor->ingest($this->logLine('Statement Multiple Store Request Validation Failure', [
            'request' => [
                'statements' => [
                    [
                        'incompatible_content_explanation' => 'Visit our {CG_link} for more information.',
                    ],
                ],
            ],
            'errors' => [
                'statement_0' => [
                    'category' => ['The selected category is invalid.'],
                ],
            ],
            'platform' => 'TikTok',
        ]).' trailing text');

        $summary = $monitor->summary();

        $this->assertSame(1, $summary['failures']);
        $this->assertSame([
            'category: The selected category is invalid.' => 1,
        ], $summary['platforms'][0]['mistakes']);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logLine(string $message, array $context): string
    {
        return '[2026-06-24 12:00:00] production.INFO: '.$message.' '.json_encode($context, JSON_THROW_ON_ERROR);
    }
}
