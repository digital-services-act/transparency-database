<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\DocumentableTrait;
use App\Console\Commands\Traits\LoggableTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Heartbeat extends Command
{
    use DocumentableTrait;
    use LoggableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heartbeat {--timeout=5 : Maximum time in seconds to wait for response}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if the database is up by performing a simple query.';

    /**
     * Command argument descriptions.
     *
     * @var array<string, string>
     */
    protected $arguments = [
        '--timeout' => 'Maximum time in seconds to wait for response',
    ];

    /**
     * Command usage examples.
     *
     * @var array<string>
     */
    protected $examples = [
        'heartbeat',
        'heartbeat --timeout=10',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $timeout = $this->option('timeout');

            // Configure database timeout if specified
            if ($timeout) {
                $driver = config('database.default');
                config(["database.connections.{$driver}.options" => [
                    \PDO::ATTR_TIMEOUT => (int) $timeout,
                ]]);
                DB::reconnect();
            }

            // Simple query to check database connectivity
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->logInfo("Database is up (took {$duration}ms)", [
                'duration_ms' => $duration,
                'timeout' => $timeout,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logError('Database connection failed', [
                'error' => $e->getMessage(),
                'timeout' => $timeout ?? null,
                'exception_class' => get_class($e),
            ]);

            return Command::FAILURE;
        }
    }
}
