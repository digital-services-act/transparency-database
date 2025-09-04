<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueView extends Command
{
    use CommandTrait;

    protected $signature = 'queue:view';

    protected $description = 'Show a report of the queue right now.';

    public function handle()
    {
        $jobs = DB::table('jobs')->get();
        $stats = [];

        foreach ($jobs as $job) {
            $queue = $job->queue ?? 'default';

            // Initialize queue stats if not exists
            if (! isset($stats[$queue])) {
                $stats[$queue] = [
                    'total' => 0,
                    'reserved' => 0,
                    'pending' => 0,
                    'multiattempts' => 0,
                    'jobs' => [],
                ];
            }

            // Count totals
            $stats[$queue]['total']++;

            // Count reserved (jobs that have reserved_at timestamp)
            if ($job->reserved_at !== null && $job->reserved_at !== '') {
                $stats[$queue]['reserved']++;
            }

            // Count multi-attempts (jobs with attempts > 1)
            if ($job->attempts > 1) {
                $stats[$queue]['multiattempts']++;
            }

            // Extract job name from payload
            $payload = json_decode($job->payload, true);
            $jobName = $payload['displayName'] ?? $payload['job'] ?? 'Unknown';
            $stats[$queue]['jobs'][] = $jobName;
        }

        // Calculate pending jobs (total - reserved)
        foreach ($stats as $queue => &$data) {
            $data['pending'] = $data['total'] - $data['reserved'];
        }

        // Sort stats by queue name for predictable output
        ksort($stats);

        $headers = ['Queue', 'Total', 'Reserved', 'Pending', 'Multi-attempts'];
        $this->info('Queues:');
        $rows = [];
        foreach ($stats as $queue => $data) {
            $rows[] = [$queue, $data['total'], $data['reserved'], $data['pending'], $data['multiattempts']];
        }
        $this->table($headers, $rows);

        // Show job details
        foreach ($stats as $queue => $data) {
            if (! empty($data['jobs'])) {
                $this->info("\nJobs in '{$queue}' queue:");
                $jobCounts = array_count_values($data['jobs']);
                ksort($jobCounts); // Sort jobs alphabetically
                foreach ($jobCounts as $jobName => $count) {
                    $this->line("  - {$jobName}: {$count}");
                }
            }
        }
    }
}
