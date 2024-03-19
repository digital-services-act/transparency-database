<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Services\StatementSearchService;
use Illuminate\Console\Command;
use JsonException;
use Symfony\Component\VarDumper\VarDumper;

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
        $platforms = [];
        $count = 0;
        $json_files = glob('storage/app/duplicated*.json');
        foreach ($json_files as $json_file) {
            $json = file_get_contents($json_file);
            $data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
            $count += count($data);
            foreach ($data as $item) {
                if (!isset($platforms[$item->platform_id])) {
                    $platforms[$item->platform_id] = 0;
                }

                ++$platforms[$item->platform_id];
            }
        }

        $this->info('Total Duplicates: ' . $count);
        foreach ($platforms as $id => $total) {
            $this->info(Platform::find($id)->name . ': ' . $total);
        }
    }
}
