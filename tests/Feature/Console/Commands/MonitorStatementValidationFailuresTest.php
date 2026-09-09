<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\MonitorStatementValidationFailures;
use App\Services\StatementValidationFailureLogMonitor;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\CreatesApplication;

class MonitorStatementValidationFailuresTest extends TestCase
{
    use CreatesApplication;

    public function test_help_displays_clever_cloud_monitoring_options(): void
    {
        $this->artisan('statements:monitor-validation-failures --help')
            ->expectsOutputToContain('Monitor statement validation failure logs and summarize the worst offending platforms.')
            ->expectsOutputToContain('--clever-app')
            ->expectsOutputToContain('--clever-bin')
            ->expectsOutputToContain('--local')
            ->assertExitCode(0);
    }

    public function test_it_monitors_a_local_log_and_writes_progress_and_empty_reports(): void
    {
        $logPath = tempnam(sys_get_temp_dir(), 'statement-monitor-');
        file_put_contents($logPath, "existing log content\n");

        try {
            $this->artisan('statements:monitor-validation-failures', [
                '--local' => true,
                '--log' => $logPath,
                '--seconds' => '2',
                '--interval' => '1',
            ])
                ->expectsOutputToContain("Monitoring {$logPath} for 00:02.")
                ->expectsOutputToContain('No platforms observed yet.')
                ->expectsOutputToContain('Final statement validation failure report')
                ->expectsOutputToContain('No matching statement validation failure logs were observed.')
                ->assertExitCode(0);
        } finally {
            unlink($logPath);
        }
    }

    public function test_it_handles_a_missing_local_log_and_minimum_option_values(): void
    {
        $logPath = sys_get_temp_dir().'/missing-statement-monitor-'.bin2hex(random_bytes(8)).'.log';

        $this->artisan('statements:monitor-validation-failures', [
            '--local' => true,
            '--log' => $logPath,
            '--minutes' => '0',
            '--interval' => '0',
        ])
            ->expectsOutputToContain("Log file does not exist yet: {$logPath}")
            ->expectsOutputToContain('No matching statement validation failure logs were observed.')
            ->assertExitCode(0);
    }

    public function test_it_monitors_stdin_without_input(): void
    {
        $wasBlocking = stream_get_meta_data(STDIN)['blocked'] ?? true;

        try {
            $this->artisan('statements:monitor-validation-failures', [
                '--stdin' => true,
                '--seconds' => '1',
                '--interval' => '0',
            ])
                ->expectsOutputToContain('Monitoring STDIN for 00:01.')
                ->expectsOutputToContain('No matching statement validation failure logs were observed.')
                ->assertExitCode(0);
        } finally {
            stream_set_blocking(STDIN, $wasBlocking);
        }
    }

    public function test_it_monitors_clever_logs_and_reports_platform_mistakes(): void
    {
        $failureLine = $this->logLine([
            'errors' => ['field' => ['The field is invalid.']],
            'platform' => 'Test platform',
        ]);
        $emptyMistakesLine = $this->logLine([
            'errors' => [],
            'platform' => 'Empty mistakes platform',
        ]);
        $scriptPath = $this->createCleverScript(
            $failureLine."\n".$emptyMistakesLine."\npartial output",
            "process warning\n"
        );

        try {
            $this->artisan('statements:monitor-validation-failures', [
                '--seconds' => '1',
                '--interval' => '1',
                '--clever-app' => 'test-app',
                '--clever-bin' => $scriptPath,
            ])
                ->expectsOutputToContain('Monitoring Clever Cloud app test-app for 00:01.')
                ->expectsOutputToContain('Total validation failure logs: 2')
                ->expectsOutputToContain('The field is invalid.')
                ->expectsOutputToContain('(none captured)')
                ->assertExitCode(0);
        } finally {
            unlink($scriptPath);
        }
    }

    public function test_it_reports_when_the_clever_logs_process_stops_early(): void
    {
        $scriptPath = $this->createCleverScript('', '', false);

        try {
            $this->artisan('statements:monitor-validation-failures', [
                '--seconds' => '1',
                '--clever-bin' => $scriptPath,
            ])
                ->expectsOutputToContain('The Clever logs process stopped before monitoring finished.')
                ->assertExitCode(1);
        } finally {
            unlink($scriptPath);
        }
    }

    public function test_it_reports_when_the_clever_logs_wrapper_cannot_run(): void
    {
        $this->artisan('statements:monitor-validation-failures', [
            '--seconds' => '1',
            '--interval' => '0',
            '--clever-app' => 'test-app',
            '--clever-bin' => '/path/that/does/not/exist',
        ])
            ->expectsOutputToContain('The Clever logs process stopped before monitoring finished.')
            ->assertExitCode(1);

        $command = $this->command([
            '--clever-app' => '',
            '--clever-bin' => '',
        ]);
        $this->assertSame(
            'app_6bf8a898-23b2-45f4-aad9-afc9ef5e583c',
            $this->invokePrivate($command, 'cleverApp')
        );
        $this->assertSame('clever', $this->invokePrivate($command, 'cleverBinary'));
    }

    public function test_it_consumes_stdin_from_a_supplied_stream(): void
    {
        $input = fopen('php://memory', 'r+');
        fwrite($input, $this->logLine([
            'errors' => ['field' => ['The field is invalid.']],
            'platform' => 'STDIN platform',
        ])."\n");
        rewind($input);

        $monitor = new StatementValidationFailureLogMonitor;
        $this->invokePrivate($this->command(), 'consumeStdin', [$monitor, $input]);

        $this->assertSame(1, $monitor->summary()['failures']);
        fclose($input);
    }

    public function test_it_consumes_new_log_lines_after_rotation_and_handles_unreadable_files(): void
    {
        $logPath = tempnam(sys_get_temp_dir(), 'statement-monitor-');
        file_put_contents($logPath, "first line\nsecond line\n");
        $command = $this->command();
        $monitor = new StatementValidationFailureLogMonitor;

        try {
            $offset = $this->invokePrivate($command, 'consumeNewLines', [$logPath, 0, $monitor]);
            $this->assertSame(filesize($logPath), $offset);

            file_put_contents($logPath, "short\n");
            $offset = $this->invokePrivate($command, 'consumeNewLines', [$logPath, PHP_INT_MAX, $monitor]);
            $this->assertSame(filesize($logPath), $offset);
        } finally {
            unlink($logPath);
        }

        $unreadablePath = tempnam(sys_get_temp_dir(), 'statement-monitor-');
        file_put_contents($unreadablePath, "unreadable\n");
        chmod($unreadablePath, 0000);

        try {
            set_error_handler(static function (): bool {
                return true;
            });

            $offset = $this->invokePrivate(
                $command,
                'consumeNewLines',
                [$unreadablePath, 0, new StatementValidationFailureLogMonitor]
            );

            $this->assertSame(0, $offset);
        } finally {
            restore_error_handler();
            chmod($unreadablePath, 0644);
            unlink($unreadablePath);
        }
    }

    public function test_it_handles_empty_and_buffered_clever_output(): void
    {
        $command = $this->command();
        $monitor = new StatementValidationFailureLogMonitor;
        $buffer = '';

        $this->invokePrivate($command, 'consumeBufferedText', ['', &$buffer, $monitor, false]);
        $this->invokePrivate($command, 'consumeBufferedText', ['', &$buffer, $monitor, true]);
        $this->invokePrivate($command, 'consumeBufferedText', ["unrelated\npartial", &$buffer, $monitor, false]);
        $this->assertSame('partial', $buffer);
        $this->invokePrivate($command, 'consumeBufferedText', ['', &$buffer, $monitor, true]);
        $this->assertSame('', $buffer);
    }

    private function command(array $options = []): MonitorStatementValidationFailures
    {
        $command = new MonitorStatementValidationFailures;
        $command->setLaravel($this->app);
        $command->setInput(new ArrayInput($options, $command->getDefinition()));
        $command->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));

        return $command;
    }

    private function invokePrivate(object $object, string $method, array $arguments = []): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);

        return $reflection->invokeArgs($object, $arguments);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function logLine(array $context): string
    {
        return '[2026-06-24 12:00:00] production.INFO: Statement Store Request Validation Failure '.json_encode($context, JSON_THROW_ON_ERROR);
    }

    private function createCleverScript(string $output, string $error = '', bool $keepRunning = true): string
    {
        $scriptPath = tempnam(sys_get_temp_dir(), 'fake-clever-');
        $script = "#!/bin/sh\nprintf '%s' ".escapeshellarg($output)."\n";

        if ($error !== '') {
            $script .= "printf '%s' ".escapeshellarg($error)." >&2\n";
        }

        if ($keepRunning) {
            $script .= "sleep 3\n";
        }

        file_put_contents($scriptPath, $script);
        chmod($scriptPath, 0755);

        return $scriptPath;
    }
}
