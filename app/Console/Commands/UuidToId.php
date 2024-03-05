<?php

namespace App\Console\Commands;

use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UuidToId extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uuid2id {uuid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the id for an uuid';

    /**
     * Execute the console command.
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        $uuid = $this->argument('uuid');
        $this->info('ID: ' . $statement_search_service->uuidToId($uuid));
    }
}
