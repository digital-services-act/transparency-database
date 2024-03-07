<?php

namespace App\Console\Commands;

use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use JsonException;

class CountDuplicates extends Command
{
    use CommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count-duplicates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the duplicates found in the files.';

    /**
     * Execute the console command.
     * @throws JsonException
     */
    public function handle(StatementSearchService $statement_search_service): void
    {
        $count = 0;
        $json_files = glob('storage/app/duplicated*.json');
        foreach ($json_files as $json_file) {
            $json = file_get_contents($json_file);
            $data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
            $count += count($data);
        }
        $this->info('Duplicates: ' . $count);
    }
}
