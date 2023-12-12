<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use JsonException;
use OpenSearch\Client;

class StatementIndexRange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $min;
    public int $max;
    public int $chunk;

    /**
     * Create a new job instance.
     */
    public function __construct(int $max, int $min, int $chunk)
    {
        $this->min = $min;
        $this->max = $max;
        $this->chunk = $chunk;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
//    public function middleware(): array
//    {
//        return [new RateLimited('reindexing')];
//    }

    /**
     * Execute the job.
     * @throws JsonException
     */
    public function handle(Client $client): void
    {
        // Set this in cache, to emergency stop reindexing.
//        $stop = Cache::get('stop_reindexing', false);

        if (!$stop) {

            $difference = $this->max - $this->min;
            // If the difference is small enough then do the searchable.
            if ($difference <= $this->chunk) {

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

                    // Call the bulk and make them searchable.
                    $client->bulk(['require_alias' => true, 'body' => implode("\n", $bulk)]);
                }

            } else {
                // The difference was too big, split it in half and dispatch those jobs.
                $break = ceil($difference / 2);
                self::dispatch($this->max, $this->max - $break, $this->chunk); // first half
                self::dispatch(($this->max - $break - 1), $this->min, $this->chunk); // second half
            }
        }
    }
}
