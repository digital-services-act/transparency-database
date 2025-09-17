<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

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
    protected $description = 'Remove a statement document from an Elasticsearch index';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        $index = $this->argument('index');
        $documentId = $this->intifyArgument('id');

        try {
            $result = $elasticSearchService->removeDocumentFromIndex($index, $documentId);

            $this->info("Document {$result['document_id']} has been successfully removed from index '{$result['index']}'.");
            $this->line("Result: {$result['result']}");
            if ($result['version']) {
                $this->line("Version: {$result['version']}");
            }
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Index does not exist')) {
                $this->error("Index '{$index}' does not exist.");
            } elseif (str_contains($e->getMessage(), 'Invalid document ID')) {
                $this->error("Invalid document ID: {$documentId}. Must be a positive integer.");
            } elseif (str_contains($e->getMessage(), 'Document not found')) {
                $this->warn("Document {$documentId} was not found in index '{$index}'.");
            } else {
                $this->error("Failed to remove document {$documentId} from index '{$index}': ".$e->getMessage());
            }
        }
    }
}
