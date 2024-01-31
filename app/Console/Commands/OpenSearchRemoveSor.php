<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use OpenSearch\Client;

class OpenSearchRemoveSor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opensearch:index-removestatement {index} {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(Client $client): void
    {
        $id = (int)$this->argument('id');
        $index = $this->argument('index');

        if (!$client->indices()->exists(['index' => $index])) {
            $this->error('Source index does not exist!');
            return;
        }

        if ($id) {
            try {
                $client->delete([
                    'index' => $index,
                    'id'    => $id
                ]);
            } catch (Exception $e) {
                $this->info($e->getMessage());
            }
        }
    }
}
