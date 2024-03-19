<?php

namespace App\Services;

use App\Models\ArchivedStatement;
use App\Models\Statement;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatementArchiveService
{
    public function archiveStatement(Statement $statement): ?ArchivedStatement
    {
        DB::beginTransaction();
        try {
            $archived_statement = ArchivedStatement::create([
                'original_id'   => $statement->id,
                'puid'          => $statement->puid,
                'platform_id'   => $statement->platform_id,
                'uuid'          => $statement->uuid,
                'date_received' => $statement->created_at
            ]);
            $statement->forceDelete(); // hard delete
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }
        DB::commit();
        return $archived_statement;
    }

    public function archiveStatements(Collection $statements): Collection
    {
        $archived_statements = collect();
        foreach ($statements as $statement) {
            $archived_statements->push($this->archiveStatement($statement));
        }
        return $archived_statements;
    }

    public function archiveStatementsFromIds(array $statement_ids): bool
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $statement_ids = array_filter($statement_ids, 'is_int');
        $ids = implode(',', $statement_ids);

        $convert_sql = "INSERT INTO archived_statements (original_id, uuid, platform_id, puid, date_received, created_at, updated_at)";
        $convert_sql .= "SELECT id, uuid, platform_id, puid, created_at, '" . $now . "', '" . $now . "' FROM statements ";
        $convert_sql .= "WHERE statements.id IN (" . $ids . ")";

        $delete_sql = "DELETE FROM statements WHERE id IN (" . $ids . ")";

        DB::beginTransaction();
        try {
            DB::statement($convert_sql);
            DB::statement($delete_sql);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Statement Archive Failure', [$e->getMessage()]);
            return false;
        }
        DB::commit();
        return true;
    }
}
