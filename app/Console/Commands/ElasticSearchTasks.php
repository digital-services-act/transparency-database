<?php

namespace App\Console\Commands;

use App\Services\StatementElasticSearchService;
use Illuminate\Console\Command;

class ElasticSearchTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get some info about the elasticsearch tasks if they can be cancelled.';

    /**
     * Execute the console command.
     */
    public function handle(StatementElasticSearchService $elasticSearchService): void
    {
        try {
            $tasksInfo = $elasticSearchService->getTasks();

            $this->info("Total tasks: {$tasksInfo['total_tasks']}");
            $this->info("Cancellable tasks: {$tasksInfo['cancellable_tasks']}");

            if ($tasksInfo['cancellable_tasks'] > 0) {
                $this->newLine();
                $this->info('Cancellable Tasks:');

                $tableData = [];
                foreach ($tasksInfo['cancellable'] as $task) {
                    $tableData[] = [
                        $task['id'],
                        $task['node'],
                        $task['type'],
                        $task['action'],
                        substr($task['description'], 0, 50).(strlen($task['description']) > 50 ? '...' : ''),
                        number_format($task['running_time'] / 1000000).'ms',
                    ];
                }

                $this->table([
                    'Task ID',
                    'Node',
                    'Type',
                    'Action',
                    'Description',
                    'Runtime',
                ], $tableData);
            } else {
                $this->info('No cancellable tasks found.');
            }
        } catch (\Exception $e) {
            $this->error('Failed to retrieve tasks: '.$e->getMessage());
        }
    }
}
