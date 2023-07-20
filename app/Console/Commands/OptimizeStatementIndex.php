<?php

namespace App\Console\Commands;

use App\Models\Statement;
use App\Models\User;
use Illuminate\Console\Command;
use OpenSearch\Client;
use Spatie\Permission\Models\Role;
use Zing\LaravelScout\OpenSearch\Engines\OpenSearchEngine;

class OptimizeStatementIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize-statement-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the Opensearch Statement Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (env('SCOUT_DRIVER', '') !== 'opensearch')
        {
            $this->error('opensearch is not the SCOUT_DRIVER');
            return;
        }

        /** @var Client $client */
        $client  = app(Client::class);

        $index_name = 'statement_' . env('APP_ENV');

        if ($client->indices()->exists(['index' => $index_name])) {
            $client->indices()->delete(['index' => $index_name]);
        };

        $properties = [
            'properties' =>
                [
                    'automated_decision'               =>
                        [
                            'type' => 'boolean'
                        ],
                    'automated_detection'              =>
                        [
                            'type' => 'boolean'
                        ],
                    'category'                         =>
                        [
                            'type' => 'keyword'
                        ],
                    'content_type'                     =>
                        [
                            'type' => 'keyword'
                        ],
                    'content_type_other'               =>
                        [
                            'type' => 'text'
                        ],
                    'created_at'                       =>
                        [
                            'type' => 'date'
                        ],
                    'decision_account'                 =>
                        [
                            'type' => 'keyword'
                        ],
                    'decision_facts'                   =>
                        [
                            'type' => 'text'
                        ],
                    'decision_ground'                  =>
                        [
                            'type' => 'keyword'
                        ],
                    'decision_monetary'                =>
                        [
                            'type' => 'keyword'
                        ],
                    'decision_provision'               =>
                        [
                            'type' => 'keyword'
                        ],
                    'decision_visibility'              =>
                        [
                            'type' => 'keyword'
                        ],
                    'id'                               =>
                        [
                            'type' => 'long'
                        ],
                    'illegal_content_explanation'      =>
                        [
                            'type' => 'text'
                        ],
                    'illegal_content_legal_ground'     =>
                        [
                            'type' => 'text'
                        ],
                    'incompatible_content_explanation' =>
                        [
                            'type' => 'text'
                        ],
                    'incompatible_content_ground'      =>
                        [
                            'type' => 'text'
                        ],
                    'platform_id'                      =>
                        [
                            'type' => 'long',
                        ],
                    'source'                           =>
                        [
                            'type' => 'text'
                        ],
                    'source_type'                      =>
                        [
                            'type' => 'keyword'
                        ],
                    'url'                              =>
                        [
                            'type' => 'text'
                        ],
                    'uuid'                             =>
                        [
                            'type' => 'text'
                        ],
                    'puid'                             =>
                        [
                            'type' => 'text'
                        ],
                    'decision_visibility_other'        =>
                        [
                            'type' => 'text'
                        ],
                    'decision_monetary_other'          =>
                        [
                            'type' => 'text'
                        ],
                    'countries_list'                   =>
                        [
                            'type' => 'text'
                        ],
                ]
        ];

        $body = [
            'mappings' => $properties,
        ];

        $client->indices()->create(['index' => $index_name, 'body' => $body]);
        Statement::makeAllSearchable();
    }

}
