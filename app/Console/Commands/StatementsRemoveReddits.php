<?php

namespace App\Console\Commands;

use App\Jobs\StatementArchiveRange;
use App\Services\DayArchiveService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use OpenSearch\Client;

class StatementsRemoveReddits extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:remove-reddits {chunk=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove statements created by reddit accidentally on the google shopping account.';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service, Client $client): void
    {
        $chunk = $this->intifyArgument('chunk');

        $google_shopping = 26;
        $search = 'reddit';

        $opensearch_result = $client->search([
            'index' => 'statement_index',
            'body' => [
                'track_total_hits' => true,
                "size" => $chunk,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'multi_match' => [
                                    'query' => $search,
                                    'fields' => [
                                        'decision_visibility_other',
                                        'decision_monetary_other',
                                        'illegal_content_legal_ground',
                                        'illegal_content_explanation',
                                        'incompatible_content_ground',
                                        'incompatible_content_explanation',
                                        'decision_facts',
                                        'content_type_other',
                                        'source_identity',
                                        'uuid',
                                        'puid'
                                    ]
                                ]
                            ],
                            [
                                'term' => [
                                    'platform_id' => [
                                        'value' => $google_shopping
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            "_source_includes" => [
                "id"
            ],
        ]);

        dd($opensearch_result);
    }
}
