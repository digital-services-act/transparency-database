<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackfillAPIController extends Controller
{
    public function statements(Request $request): JsonResponse
    {
        $bulkInsertData = $request->input('statements', []);

        if ($bulkInsertData !== []) {
            DB::table($this->table())->insert($bulkInsertData);
        }

        return response()->json([
            'message' => 'ok',
            'inserted' => count($bulkInsertData),
        ]);
    }

    public function lastImportedId(): JsonResponse
    {
        $lastImportedId = DB::table($this->table())
            ->where('id', '<', $this->endId())
            ->where('id', '>', $this->startId())
            ->max('id');

        if (! $lastImportedId) {
            $lastImportedId = $this->startId();
        }    

        return response()->json([
            'last_imported_id' => $lastImportedId,
        ]);
    }

    public function highestImportedId(): JsonResponse
    {
        return $this->lastImportedId();
    }

    private function startId(): int
    {
        return (int) config('backfill.start_id');
    }

    private function endId(): int
    {
        return (int) config('backfill.end_id');
    }

    private function table(): string
    {
        return (string) config('backfill.table');
    }
}
