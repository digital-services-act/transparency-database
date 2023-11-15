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
    protected $signature = 'statements:optimize-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the Opensearch Statements Index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Client $client */
        $client     = app(Client::class);
        $index_name = 'statement_' . config('app.env');

        if ($client->indices()->exists(['index' => $index_name])) {
            $client->indices()->delete(['index' => $index_name]);
        }

        $properties = [
            'properties' =>
                [
                    'automated_decision'               =>
                        [
                            'type' => 'keyword'
                        ],
                    'automated_detection'              =>
                        [
                            'type' => 'boolean'
                        ],
                    'category'                         =>
                        [
                            'type' => 'keyword'
                        ],
                    'category_specification'           =>
                        [
                            'type' => 'text'
                        ],
                    'content_type'                     =>
                        [
                            'type' => 'text'
                        ],
                    'content_type_other'               =>
                        [
                            'type' => 'text'
                        ],
                    'content_language'                 =>
                        [
                            'type' => 'keyword'
                        ],
                    'created_at'                       =>
                        [
                            'type' => 'date'
                        ],
                    'created_at_date'                  =>
                        [
                            'type' => 'date'
                        ],
                    'content_date'                     =>
                        [
                            'type' => 'date'
                        ],
                    'application_date'                 =>
                        [
                            'type' => 'date'
                        ],
                    'decision_account'                 =>
                        [
                            'type' => 'keyword'
                        ],
                    'account_type'                     =>
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
                            'type' => 'text'
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
                    'platform_name'                    =>
                        [
                            'type' => 'text',
                        ],
                    'platform_uuid'                    =>
                        [
                            'type' => 'text',
                        ],
                    'source_identity'                  =>
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
                    'territorial_scope'                =>
                        [
                            'type' => 'text'
                        ],
                ]
        ];

        $body = [
            'mappings' => $properties,
        ];

        $client->indices()->create(['index' => $index_name, 'body' => $body]);
    }

}
