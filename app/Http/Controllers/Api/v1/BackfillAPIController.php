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
        return $this->importedId($this->direction());
    }

    public function highestImportedId(): JsonResponse
    {
        return $this->importedId('asc');
    }

    private function importedId(string $direction): JsonResponse
    {
        $query = DB::table($this->table())
            ->where('id', '<', $this->endId())
            ->where('id', '>', $this->startId());

        $highestImportedId = (clone $query)->max('id');
        $lowestId = (clone $query)->min('id');

        $lastImportedId = $direction === 'desc'
            ? ($lowestId ?? $this->endId())
            : ($highestImportedId ?? $this->startId());

        return response()->json([
            'last_imported_id' => (int) $lastImportedId,
            'highest_imported_id' => $highestImportedId === null ? null : (int) $highestImportedId,
            'lowest_id' => $lowestId === null ? null : (int) $lowestId,
            'direction' => $direction,
        ]);
    }

    private function direction(): string
    {
        $direction = strtolower(trim((string) config('backfill.direction', 'desc')));

        return $direction === 'asc' ? 'asc' : 'desc';
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
