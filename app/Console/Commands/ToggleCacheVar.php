<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\DocumentableTrait;
use App\Console\Commands\Traits\LoggableTrait;
use App\Services\CommandValidationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ToggleCacheVar extends Command
{
    use DocumentableTrait;
    use LoggableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'toggle-cache-var {key} {state}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle the bool state of a cache key.';

    /**
     * Command argument descriptions.
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'key' => 'The cache key to toggle',
        'state' => 'The state to set (true/false)',
    ];

    /**
     * Command usage examples.
     *
     * @var array<string>
     */
    protected $examples = [
        'toggle-cache-var feature_flag true',
        'toggle-cache-var maintenance_mode false',
    ];

    /**
     * Execute the console command.
     */
    public function handle(CommandValidationService $validator): void
    {
        try {
            $key = $validator->validateRequiredArgument($this, 'key');

            try {
                $state = $validator->validateBooleanArgument($this, 'state');
            } catch (\RuntimeException $e) {
                // Default to false for invalid state values
                $state = false;
                $this->logWarning(
                    'Invalid state value provided, defaulting to false',
                    ['key' => $key, 'state' => $this->argument('state')]
                );
            }

            Cache::forever($key, $state);

            $this->logInfo(
                "Cache key '{$key}' set to: ".($state ? 'true' : 'false'),
                ['key' => $key, 'state' => $state]
            );
        } catch (\Exception $e) {
            $this->logError(
                "Failed to set cache: {$e->getMessage()}",
                ['key' => $key ?? null, 'error' => $e->getMessage()]
            );
        }
    }
}
