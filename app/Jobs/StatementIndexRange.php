<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use JsonException;
use OpenSearch\Client;

class StatementIndexRange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $min;
    public int $max;
    public bool $log_when_done;

    /**
     * Create a new job instance.
     */
    public function __construct(int $min, int $max, bool $log_when_done = false)
    {
        $this->min = $min;
        $this->max = $max;
        $this->log_when_done = $log_when_done;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('reindexing')];
    }

    /**
     * Execute the job.
     * @throws JsonException
     */
    public function handle(Client $client): void
    {
        $statements = Statement::query()->where('id', '>=', $this->min)->where('id', '<=', $this->max)->get();
        if ($statements->count()) {
            $bulk = [];
            /** @var Statement $statement */
            foreach ($statements as $statement) {
                $doc    = $statement->toSearchableArray();
                $bulk[] = json_encode([
                    'index' => [
                        '_index' => 'statement_index',
                        '_id'    => $statement->id
                    ]
                ], JSON_THROW_ON_ERROR);

                $bulk[] = json_encode($doc, JSON_THROW_ON_ERROR);
            }

            $client->bulk(['require_alias' => true, 'body' => implode("\n", $bulk)]);
            if ($this->log_when_done) {
                Log::info('Statement Index Done!');
            }
        }
    }
}
