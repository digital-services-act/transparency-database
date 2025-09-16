<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchTasksCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:tasks-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel any tasks that can be cancelled.';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        try {
            $result = $elasticSearchService->cancelAllTasks();

            if ($result['cancelled_tasks'] > 0) {
                $this->info("Successfully cancelled {$result['cancelled_tasks']} cancellable task(s).");
            } else {
                $this->info('No cancellable tasks found to cancel.');
            }

            if (! $result['acknowledged']) {
                $this->warn('Task cancellation was not acknowledged by Elasticsearch.');
            }
        } catch (\Exception $e) {
            $this->error('Failed to cancel tasks: '.$e->getMessage());
        }
    }
}
