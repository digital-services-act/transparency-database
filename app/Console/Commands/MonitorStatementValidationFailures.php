<?php

namespace App\Console\Commands;

use App\Services\StatementValidationFailureLogMonitor;
use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessStartFailedException;
use Symfony\Component\Process\Process;

class MonitorStatementValidationFailures extends Command
{
    private const string DEFAULT_CLEVER_APP = 'app_6bf8a898-23b2-45f4-aad9-afc9ef5e583c';

    protected $signature = 'statements:monitor-validation-failures
        {--minutes=2 : Number of minutes to monitor}
        {--seconds= : Override monitoring duration in seconds}
        {--interval=5 : Seconds between progress updates}
        {--clever-app=app_6bf8a898-23b2-45f4-aad9-afc9ef5e583c : Clever Cloud application ID or name to monitor}
        {--clever-bin=clever : Clever Tools executable}
        {--stdin : Read log lines from STDIN instead of a local log file}
        {--local : Read a local Laravel log file instead of Clever Cloud logs}
        {--log= : Log file path to monitor}';

    protected $description = 'Monitor statement validation failure logs and summarize the worst offending platforms.';

    private string $cleverOutputBuffer = '';

    private string $cleverErrorBuffer = '';

    public function handle(StatementValidationFailureLogMonitor $monitor): int
    {
        $logPath = $this->logPath();
        $durationSeconds = $this->durationSeconds();
        $intervalSeconds = $this->intervalSeconds();
        $startedAt = microtime(true);
        $endsAt = $startedAt + $durationSeconds;
        $nextReportAt = $startedAt + $intervalSeconds;
        $readFromStdin = (bool) $this->option('stdin');
        $readFromLocalLog = $this->shouldReadFromLocalLog();
        $cleverProcess = null;
        $offset = ($readFromStdin || ! $readFromLocalLog) ? 0 : $this->initialOffset($logPath);

        if ($readFromStdin) {
            $this->info("Monitoring STDIN for {$this->formatSeconds($durationSeconds)}.");
        } elseif ($readFromLocalLog) {
            $this->info("Monitoring {$logPath} for {$this->formatSeconds($durationSeconds)}.");
        } else {
            $cleverProcess = $this->startCleverLogsProcess($this->cleverApp());

            if (! $cleverProcess instanceof Process) {
                return Command::FAILURE;
            }

            $this->info("Monitoring Clever Cloud app {$this->cleverApp()} for {$this->formatSeconds($durationSeconds)}.");
        }

        $this->line('Only new matching validation failure log entries will be counted.');

        if ($readFromStdin) {
            stream_set_blocking(STDIN, false);
        }

        $exitCode = Command::SUCCESS;

        while (microtime(true) < $endsAt) {
            if ($readFromStdin) {
                $this->consumeStdin($monitor);
            } elseif ($cleverProcess instanceof Process) {
                $this->consumeCleverLogsProcess($cleverProcess, $monitor);

                if (! $cleverProcess->isRunning()) {
                    $this->error('The Clever logs process stopped before monitoring finished.');
                    $this->line(trim($cleverProcess->getErrorOutput()));
                    $exitCode = Command::FAILURE;
                    break;
                }
            } else {
                $offset = $this->consumeNewLines($logPath, $offset, $monitor);
            }

            $now = microtime(true);

            if ($now >= $nextReportAt) {
                $this->writeProgressReport($monitor, (int) max(0, ceil($endsAt - $now)));
                $nextReportAt = $now + $intervalSeconds;
            }

            usleep(200_000);
        }

        if ($readFromStdin) {
            $this->consumeStdin($monitor);
        } elseif ($cleverProcess instanceof Process) {
            $this->consumeCleverLogsProcess($cleverProcess, $monitor, true);
            $cleverProcess->stop(1);
        } else {
            $this->consumeNewLines($logPath, $offset, $monitor);
        }
        $this->newLine();
        $this->writeFinalReport($monitor);

        return $exitCode;
    }

    private function logPath(): string
    {
        $log = $this->option('log');

        if (is_string($log) && trim($log) !== '') {
            return $log;
        }

        return storage_path('logs/laravel.log');
    }

    private function shouldReadFromLocalLog(): bool
    {
        $log = $this->option('log');

        return (bool) $this->option('local')
            || (is_string($log) && trim($log) !== '');
    }

    private function cleverApp(): string
    {
        $app = $this->option('clever-app');

        if (! is_string($app) || trim($app) === '') {
            return self::DEFAULT_CLEVER_APP;
        }

        return trim($app);
    }

    private function cleverBinary(): string
    {
        $binary = $this->option('clever-bin');

        if (! is_string($binary) || trim($binary) === '') {
            return 'clever';
        }

        return trim($binary);
    }

    private function durationSeconds(): int
    {
        $seconds = $this->option('seconds');

        if ($seconds !== null && $seconds !== '') {
            return max(1, (int) $seconds);
        }

        return max(1, (int) round(((float) $this->option('minutes')) * 60));
    }

    private function intervalSeconds(): int
    {
        return max(1, (int) $this->option('interval'));
    }

    private function initialOffset(string $logPath): int
    {
        clearstatcache(true, $logPath);

        if (! is_file($logPath)) {
            $this->warn("Log file does not exist yet: {$logPath}");

            return 0;
        }

        return (int) filesize($logPath);
    }

    private function consumeNewLines(string $logPath, int $offset, StatementValidationFailureLogMonitor $monitor): int
    {
        clearstatcache(true, $logPath);

        if (! is_file($logPath)) {
            return 0;
        }

        $size = (int) filesize($logPath);

        if ($size < $offset) {
            $offset = 0;
        }

        if ($size === $offset) {
            return $offset;
        }

        $handle = fopen($logPath, 'rb');

        if ($handle === false) {
            $this->warn("Unable to open log file: {$logPath}");

            return $offset;
        }

        fseek($handle, $offset);

        while (($line = fgets($handle)) !== false) {
            $monitor->ingest($line);
        }

        $offset = (int) ftell($handle);
        fclose($handle);

        return $offset;
    }

    private function consumeStdin(StatementValidationFailureLogMonitor $monitor): void
    {
        while (($line = fgets(STDIN)) !== false) {
            $monitor->ingest($line);
        }
    }

    private function startCleverLogsProcess(string $app): ?Process
    {
        $process = new Process([
            $this->cleverBinary(),
            'logs',
            '--app',
            $app,
            '--no-color',
        ]);
        $process->setTimeout(null);

        try {
            $process->start();
        } catch (ProcessStartFailedException $exception) {
            $this->error("Unable to start Clever logs: {$exception->getMessage()}");

            return null;
        }

        return $process;
    }

    private function consumeCleverLogsProcess(
        Process $process,
        StatementValidationFailureLogMonitor $monitor,
        bool $flush = false
    ): void {
        $this->consumeBufferedText($process->getIncrementalOutput(), $this->cleverOutputBuffer, $monitor, $flush);
        $this->consumeBufferedText($process->getIncrementalErrorOutput(), $this->cleverErrorBuffer, $monitor, $flush);
    }

    private function consumeBufferedText(
        string $text,
        string &$buffer,
        StatementValidationFailureLogMonitor $monitor,
        bool $flush = false
    ): void {
        if ($text === '' && ! $flush) {
            return;
        }

        $buffer .= $text;

        if ($buffer === '') {
            return;
        }

        if ($flush) {
            $lines = preg_split('/\R/', $buffer);
            $buffer = '';
        } else {
            $lines = preg_split('/\R/', $buffer);

            if ($lines === false) {
                return;
            }

            $buffer = (string) array_pop($lines);
        }

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            if ($line !== '') {
                $monitor->ingest($line);
            }
        }
    }

    private function writeProgressReport(StatementValidationFailureLogMonitor $monitor, int $secondsRemaining): void
    {
        $summary = $monitor->summary();

        $this->newLine();
        $this->line(sprintf(
            '[%s] %s left | %d validation failure logs | %d validation mistakes',
            now()->format('H:i:s'),
            $this->formatSeconds($secondsRemaining),
            $summary['failures'],
            $summary['mistakes'],
        ));

        $this->writePlatformTable($monitor->topPlatforms(5), 1);
    }

    private function writeFinalReport(StatementValidationFailureLogMonitor $monitor): void
    {
        $summary = $monitor->summary();

        $this->info('Final statement validation failure report');
        $this->line("Total validation failure logs: {$summary['failures']}");
        $this->line("Total validation mistakes: {$summary['mistakes']}");
        $this->line("Single endpoint failures: {$summary['endpoints']['single']}");
        $this->line("Multiple endpoint failures: {$summary['endpoints']['multiple']}");

        $this->newLine();

        if ($summary['failures'] === 0) {
            $this->line('No matching statement validation failure logs were observed.');

            return;
        }

        $this->writePlatformTable($monitor->topPlatforms(5), 3);
    }

    /**
     * @param  array<int, array{platform: string, attempts: int, mistake_count: int, mistakes: array<string, int>}>  $platforms
     */
    private function writePlatformTable(array $platforms, int $mistakeLimit): void
    {
        if ($platforms === []) {
            $this->line('No platforms observed yet.');

            return;
        }

        $rows = [];

        foreach ($platforms as $index => $platform) {
            $rows[] = [
                $index + 1,
                $platform['platform'],
                $platform['attempts'],
                $platform['mistake_count'],
                $this->formatTopMistakes($platform['mistakes'], $mistakeLimit),
            ];
        }

        $this->table(['#', 'Platform', 'Attempts', 'Mistakes', 'Top mistakes'], $rows);
    }

    /**
     * @param  array<string, int>  $mistakes
     */
    private function formatTopMistakes(array $mistakes, int $limit): string
    {
        if ($mistakes === []) {
            return '(none captured)';
        }

        $formatted = [];

        foreach (array_slice($mistakes, 0, $limit, true) as $mistake => $count) {
            $formatted[] = "{$mistake} ({$count})";
        }

        return implode('; ', $formatted);
    }

    private function formatSeconds(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }
}
