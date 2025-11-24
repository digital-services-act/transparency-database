<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;
use Carbon\Carbon;
use Exception;


/**
 * @codeCoverageIgnore
 */
class OpenSearchTasks extends Command
{
    protected $signature = 'opensearch:tasks
                            {--long : Show only tasks running longer than 5 minutes.}
                            {--cancelled : Show cancelled tasks.}
                            {--completed : Show tasks that have been canceled or completed.}
                            {--parents : Show only parent tasks, including their total child count and runtime.}';

    protected $description = 'Displays a summary of running and completed OpenSearch tasks, filtering out canceled tasks by default.';

    // Define the threshold for a task that is likely "finished" but still in the list (e.g., running time less than 1 second)
    const MINIMAL_RUNTIME_NANOS = 1000000000;
    const LONG_RUNNING_THRESHOLD_SECONDS = 300;

    public function handle(): void
    {
        /** @var Client $client */
        $client = app(Client::class);

        try {
            // Fetch detailed information for long-running task types
            $tasklist = $client->tasks()->tasksList(['detailed' => true]);
        } catch (Exception $e) {
            $this->error("❌ Error connecting to OpenSearch: " . $e->getMessage());
            return;
        }

        // Process and filter the raw task list
        $allTasks = $this->processRawTasks($tasklist);

        // Apply the requested filtering and aggregation
        $tasksToDisplay = $this->getFilteredTasks($allTasks);

        if (empty($tasksToDisplay)) {
            $this->info("🎉 No tasks found matching your criteria.");
            return;
        }

        // Display the results based on the options
        if ($this->option('parents')) {
            $this->displayParentsTable($tasksToDisplay);
        } else {
            $this->displayTasksTable($tasksToDisplay);
        }
    }

    /**
     * Processes raw OpenSearch task data into a clean array structure.
     */
    protected function processRawTasks(array $tasklist): array
    {
        $processedTasks = [];
        foreach ($tasklist['nodes'] as $nodeId => $nodeInfo) {
            foreach ($nodeInfo['tasks'] as $taskId => $taskInfo) {

                $runningTimeSeconds = ($taskInfo['running_time_in_nanos'] ?? 0) / 1000000000;
                $formattedTime = $runningTimeSeconds >= 3600
                    ? round($runningTimeSeconds / 3600, 2) . ' hours'
                    : round($runningTimeSeconds / 60, 2) . ' minutes';

                $idNodeParts = explode(':', $taskId);
                $parentId = array_key_exists('parent_task_id', $taskInfo) ?
                    explode(':', $taskInfo['parent_task_id'])[1]
                    : null;

                $processedTasks[] = [
                    'id'             => $idNodeParts[1],
                    'node'       => $idNodeParts[0],
                    'type'           => explode('[', $taskInfo['action'])[0],
                    'action'         => $taskInfo['action'],
                    'parent_task_id'      => $parentId,
                    'is_parent'      => (bool) $parentId,
                    'running_time_sec' => $runningTimeSeconds,
                    'running_time'   => $formattedTime,
                    'cancelled'      => $taskInfo['cancelled'] ?? false,
                    'status'         => $taskInfo['status'] ?? [],
                ];
            }
        }
        return $processedTasks;
    }

    /**
     * Applies filtering logic based on command options.
     */
    protected function getFilteredTasks(array $tasks): array
    {
        // 1. Apply --long filter
        if ($this->option('long')) {
            $this->comment("🔍 Displaying tasks running longer than " . self::LONG_RUNNING_THRESHOLD_SECONDS . " seconds...");
            $tasks = array_filter($tasks, fn($t) => $t['running_time_sec'] >= self::LONG_RUNNING_THRESHOLD_SECONDS);
        }

        // 2. Apply --cancelled filter
        if ($this->option('cancelled')) {
            $this->comment("🔍 Displaying cancelled tasks...");
            $tasks = array_filter($tasks, fn($t) => $t['cancelled'] === true);
        }

        // 3. Apply --completed filter
        if ($this->option('completed')) {
            $this->comment("🔍 Displaying tasks that have been canceled or are near completion...");
            // Keep tasks that are *not* currently active (e.g., those stored in the task list for cleanup)
            $tasks = array_filter($tasks, fn($t) => $t['running_time_sec'] < 60 && $t['running_time_sec'] > 0);
        }

        // 3. Apply --parents aggregation
        if ($this->option('parents')) {
            return $this->aggregateParents($tasks);
        }

        // Sort by longest running time first
        usort($tasks, fn($a, $b) => $b['running_time_sec'] <=> $a['running_time_sec']);

        return $tasks;
    }

    /**
     * Aggregates tasks into parent/child structure for the --parents view.
     */
    protected function aggregateParents(array $tasks): array
    {
        $parents = [];
        $childrenCount = [];

        foreach ($tasks as $task) {
            if ($task['parent_task_id']) {
                $parentId = $task['parent_task_id'];
                $childrenCount[$parentId] = ($childrenCount[$parentId] ?? 0) + 1;
            } else {
                $parents[$task['id']] = $task;
            }
        }

        $parentTasks = [];
        foreach ($parents as $parentId => $parentTask) {
            $parentTask['child_count'] = $childrenCount[$parentId] ?? 0;
            $parentTasks[] = $parentTask;
        }

        $this->comment("🔍 Displaying Parent Tasks with Child Counts...");

        // Sort parents by longest running time
        usort($parentTasks, fn($a, $b) => $b['running_time_sec'] <=> $a['running_time_sec']);

        return $parentTasks;
    }

    /**
     * Shows a standard table for running/completed tasks.
     */
    protected function displayTasksTable(array $tasks): void
    {
        $headers = ['*', 'Type', 'Running Time', 'Parent ID', 'Node', 'Action/Description'];
        $rows = [];

        $i = 1;
        foreach ($tasks as $task) {
            $rows[] = [
                $i,
                $task['type'],
                $task['running_time'],
                $task['parent_task_id'],
                $task['node'],
                $task['action'],
            ];
            $i++;
        }

        $this->info("Total Tasks Displayed: " . count($rows));
        $this->table($headers, $rows);
        $this->warn("\n💡 The list is sorted by running time, longest first.");
    }

    /**
     * Shows a table for parent tasks.
     */
    protected function displayParentsTable(array $tasks): void
    {
        $headers = ['*', 'Type', 'Running Time', 'Child Count', 'Node', 'Action/Description'];
        $rows = [];

        $i = 1;
        foreach ($tasks as $task) {
            $rows[] = [
                $i,
                $task['type'],
                $task['running_time'],
                number_format($task['child_count']),
                $task['node'],
                $task['action'],
            ];
            $i++;
        }

        $this->info("Total Parent Tasks Displayed: " . count($rows));
        $this->table($headers, $rows);
        $this->warn("\n💡 Parent tasks are those that manage sub-tasks (e.g., reindex with multiple slices).");
    }
}
