<?php

namespace App\Console\Commands;

use App\Models\Platform;
use OpenSearch\Client as OpenSearch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * @codeCoverageIgnore
**/
class StatementsFetchFaultyIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:fetch-faulty-ids {platform?} {--dry-run} {--batch=1000}';
    protected $description = 'Fix faulty PUIDs for a platform and record affected dates in Redis for later CSV regenration';

    /**
     * Execute the console command.
     */
    public function handle(OpenSearch $opensearch)
    {
        $batchSize = (int) $this->option('batch');
        $platform = $this->argument('platform') ?? 'Discord Netherlands B.V.';
        $platform = Platform::where('name', $platform)->first();

        if (!$platform) {
            $this->error("Could not find platform " . $platform);
            return;
        }

        // get ids from opensearch and store them in our table (faulty_ids)
        $cursor = null;
        $page = 1;
        $params = ['fetch_size' => $batchSize];

        $query = <<<SQL
            SELECT
                %s
            FROM statement_index
            WHERE platform_id = $platform->id
              AND created_at < '2025-08-15 00:00:00'
            ORDER BY created_at ASC
        SQL;

        $totalCount = $opensearch->sql()->query([
            'query' => sprintf($query, 'count(id)')
        ])['datarows'][0][0];
        $totalInserted = 0;

        do {
            if ($cursor) {
                $params = ['cursor' => $cursor];
            } else {
                $params = ['query' => sprintf($query, 'id, created_at'), ...$params];
            }

            $response = $opensearch->sql()->query($params);

            if (!isset($response['datarows']) || empty($response['datarows'])) {
                $this->warn("⚠️ No datarows returned");
                break;
            }

            $rows = array_map(fn ($row) => [
                'id'         => $row[0],
                'created_at' => $row[1],
                'source_table' => $row[1] < '2025-07-01' ? 'statements' : 'statements_beta',
            ], $response['datarows']);

            DB::table('faulty_ids')->insert($rows);

            $inserted = count($rows);
            $totalInserted += $inserted;

            $this->info("📥 Inserted {$inserted} records (page {$page} out of " . ceil($totalCount / $batchSize) . "). Total: {$totalInserted}");
            $cursor = $response['cursor'] ?? null;
            $page++;
        } while ($cursor);
    }
}
