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
                            'type' => 'boolean',
                        ],
                    'automated_detection'              =>
                        [
                            'type' => 'boolean',
                        ],
                    'category'                         =>
                        [
                            'type'   => 'keyword',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'content_type'                     =>
                        [
                            'type'   => 'keyword',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'content_type_other'               =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'created_at'                       =>
                        [
                            'type' => 'date',
                        ],
                    'decision_account'                 =>
                        [
                            'type'   => 'keyword',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'decision_facts'                   =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'decision_ground'                  =>
                        [
                            'type'   => 'keyword',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'decision_monetary'                =>
                        [
                            'type'   => 'keyword',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'decision_provision'               =>
                        [
                            'type'   => 'keyword',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'decision_visibility'              =>
                        [
                            'type'   => 'keyword',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'id'                               =>
                        [
                            'type' => 'long',
                        ],
                    'illegal_content_explanation'      =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'illegal_content_legal_ground'     =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'incompatible_content_explanation' =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'incompatible_content_ground'      =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'platform_id'                      =>
                        [
                            'type' => 'long',
                        ],
                    'source'                           =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'source_type'                      =>
                        [
                            'type'   => 'keyword',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'url'                           =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'uuid'                           =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'puid'                           =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'decision_visibility_other'                           =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
                        ],
                    'decision_monetary_other'                           =>
                        [
                            'type'   => 'text',
                            'fields' =>
                                [
                                    'keyword' =>
                                        [
                                            'type'         => 'keyword',
                                            'ignore_above' => 256,
                                        ]
                                ]
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
