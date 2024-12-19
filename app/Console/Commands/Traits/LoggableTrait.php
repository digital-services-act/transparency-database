<?php

namespace App\Console\Commands\Traits;

use Illuminate\Support\Facades\Log;

trait LoggableTrait
{
    protected function logInfo(string $message, array $context = []): void
    {
        $commandName = class_basename($this);
        Log::info("[{$commandName}] {$message}", $context);
        $this->info($message);
    }

    protected function logError(string $message, array $context = []): void
    {
        $commandName = class_basename($this);
        Log::error("[{$commandName}] {$message}", $context);
        $this->error($message);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        $commandName = class_basename($this);
        Log::warning("[{$commandName}] {$message}", $context);
        $this->warn($message);
    }
}
