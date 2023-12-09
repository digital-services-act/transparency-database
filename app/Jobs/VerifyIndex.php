<?php

namespace App\Jobs;

use App\Models\Statement;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client;

class VerifyIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $start;
    public int $chunk;
    public int $min;

    /**
     * Create a new job instance.
     */
    public function __construct(int $start, int $chunk, int $min)
    {
        $this->start = $start;
        $this->min = $min;
        $this->chunk = $chunk;
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
     * @throws Exception
     */
    public function handle(Client $client): void
    {

        $end = max( ($this->start - $this->chunk), $this->min);
        

        Log::info('Verifying Index: ' . $this->start . ' :: ' . $end . " :: " . $this->chunk);

        $db_count = Statement::query()->where('id', '<=', $this->start)->where('id', '>', $end)->count();
        $opensearch_query = [
            "query" => [
                "bool" => [
                    "filter" => [
                            [
                                "range" => [
                                    "id" => [
                                        "from" => $end,
                                        "to" => $this->start,
                                        "include_lower" => false,
                                        "include_upper" => true,
                                        "boost" => 1.0
                                    ]
                                ]
                            ]
                    ],
                "adjust_pure_negative" => true,
                "boost" => 1.0
                ]
            ]
        ];

        $opensearch_count = $client->count([
            'index' => 'statement_index',
            'body'  => $opensearch_query,
        ])['count'] ?? -1;

        if ($db_count > $opensearch_count) {
            Log::info('Missing Statements in  Index: ' . $this->start . ' to ' . $end . ' off by ' . ($db_count - $opensearch_count));
            if ($this->chunk <= 1000) {
                $range = range($this->start, $end);
                StatementIndexRange::dispatch($end, $this->start);
            } else {
                self::dispatch($this->start, floor($this->chunk / 10), $end);
            }
        }

        if ($end > $this->min) {
            self::dispatch($end - 1, $this->chunk, $this->min);
        }

        if ($end <= $this->min) {
            Log::info('Finished Verifying Index to: ' . $end);
        }
    }
}
