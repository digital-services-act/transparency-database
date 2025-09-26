<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenSearch\Client;

/**
 * @codeCoverageIgnore
 */
class OpenSearchDeleteBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $index, protected array $query, protected int $batchSize = 3000)
    {
    }

    public function handle(Client $opensearch): void
    {
        $response = $opensearch->deleteByQuery([
            'index'     => $this->index,
            'conflicts' => 'proceed',
            'body'      => ['query' => $this->query],
            'size'      => $this->batchSize,
            'refresh'   => true,
        ]);

        $deleted = $response['deleted'] ?? 0;
        logger()->info("🗑 Deleted {$deleted} docs from {$this->index}");
    }
}
