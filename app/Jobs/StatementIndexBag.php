<?php

namespace App\Jobs;

use App\Models\Statement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use JsonException;
use OpenSearch\Client;

class StatementIndexBag implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $statement_ids;


    /**
     * Create a new job instance.
     */
    public function __construct($statement_ids)
    {
        $this->statement_ids = $statement_ids;
    }

    /**
     * Execute the job.
     * @throws JsonException
     */
    public function handle(Client $client): void
    {
        // Set this in cache, to emergency stop reindexing.
        $stop = Cache::get('stop_reindexing', false);

        if ( ! $stop) {
            $statements = Statement::query()->whereIn('id', $this->statement_ids)->get();
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
        }
    }
}
