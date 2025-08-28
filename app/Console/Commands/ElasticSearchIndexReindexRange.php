<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class ElasticSearchIndexReindexRange extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-reindex-range {index} {target} {first} {last}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex an index into another Elasticsearch Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(StatementElasticSearchService::class)->client();
        $index = $this->argument('index');
        $target = $this->argument('target');
        $first = $this->intifyArgument('first');
        $last = $this->intifyArgument('last');

        if (! $client->indices()->exists(['index' => $index])->asBool()) {
            $this->warn('Source index does not exist!');

            return;
        }

        if (! $client->indices()->exists(['index' => $target])->asBool()) {
            $this->warn('Target index does not exist!');

            return;
        }

        $result = $client->reindex([
            'wait_for_completion' => false,
            'body' => [
                'conflicts' => 'proceed',
                'source' => [
                    'index' => $index,
                    'query' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'range' => [
                                        'id' => [
                                            'from' => $first,
                                            'to' => $last,
                                            'include_lower' => true,
                                            'include_upper' => true,
                                            'boost' => 1.0,
                                        ],
                                    ],
                                ],
                            ],
                            'adjust_pure_negative' => true,
                            'boost' => 1.0,
                        ],
                    ],
                ],
                'dest' => [
                    'index' => $target,
                    'op_type' => 'create',
                ],
            ],
        ]);
    }
}
