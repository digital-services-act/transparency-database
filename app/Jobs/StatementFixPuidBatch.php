<?php

namespace App\Jobs;

use App\Models\Statement;
use App\Models\StatementAlpha;
use App\Services\StatementSearchService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @codeCoverageIgnore
**/
class StatementFixPuidBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batchNr;
    protected $ids;
    protected $table;

    public function __construct(string $table, array $ids, int $batchNr)
    {
        $this->batchNr = $batchNr;
        $this->ids = $ids;
        $this->table = $table;
    }

    public function handle(StatementSearchService $opensearch)
    {
        $start = Carbon::now();
        $idsStr = implode(',', $this->ids);

        // Update DB
        $query = <<<SQL
            UPDATE {$this->table}
            SET puid = SUBSTRING_INDEX(puid, '-', 1)
            WHERE id in ({$idsStr})
        SQL;

        DB::unprepared($query);

        // Reload updated statements for OpenSearch
        $updatedStatements = $this->table === 'statements'
            ? StatementAlpha::whereIn('id', $this->ids)->get()->makeVisible('puid')
            : Statement::whereIn('id', $this->ids)->get()->makeVisible('puid');

        $opensearch->bulkIndexStatements($updatedStatements, true);

        // Mark in faulty_ids
        DB::table('faulty_ids')
            ->whereIn('id', $this->ids)
            ->update([
                'updated_db_at' => now(),
                'updated_os_at' => now(),
            ]);

        Log::info("Finished batch {$this->batchNr} for {$this->table}. Duration: " . Carbon::now()->diffForHumans($start));
    }
}
