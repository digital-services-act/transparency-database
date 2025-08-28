<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class ElasticSearchRemoveSor extends Command
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:index-removestatement {index} {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client = app(StatementElasticSearchService::class)->client();
        $id = $this->intifyArgument('id');
        $index = $this->argument('index');

        if (! $client->indices()->exists(['index' => $index])->asBool()) {
            $this->error('Source index does not exist!');

            return;
        }

        if ($id !== 0) {
            try {
                $client->delete([
                    'index' => $index,
                    'id' => $id,
                ]);
            } catch (Exception $e) {
                $this->info($e->getMessage());
            }
        }
    }
}
