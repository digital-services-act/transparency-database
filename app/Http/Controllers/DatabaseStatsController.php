<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class DatabaseStatsController extends Controller
{
    public function index(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        // Get the time range - default to 10 minutes
        $minutes = $request->input('minutes', 10);
        $timeAgo = Carbon::now()->subMinutes($minutes);

        // Query 1: Get statements per second for the specified time range, ordered by count desc
        $statementsPerSecond = DB::table('statements')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as second"), DB::raw('COUNT(*) as rps'))
            ->where('created_at', '>=', $timeAgo)
            ->where('id', '>', 1)
            ->groupBy('second')
            ->orderBy('rps', 'desc')
            ->get();

        // If no results, try with a longer time range for testing
        if ($statementsPerSecond->isEmpty() && $minutes <= 10) {
            $extendedTimeAgo = Carbon::now()->subHours(24);
            $statementsPerSecond = DB::table('statements')
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as second"), DB::raw('COUNT(*) as rps'))
                ->where('created_at', '>=', $extendedTimeAgo)
                ->where('id', '>', 1)
                ->groupBy('second')
                ->orderBy('rps', 'desc')
                ->get();
        }

        // Query 2: Get elapsed time between min and max created_at
        $timeRange = DB::table('statements')
            ->select(
                DB::raw('MIN(created_at) as min_time'),
                DB::raw('MAX(created_at) as max_time')
            )
            ->where('created_at', '>=', $timeAgo)
            ->where('id', '>', 1)
            ->first();

        $elapsedTime = null;
        if ($timeRange && $timeRange->min_time && $timeRange->max_time) {
            $minTime = Carbon::parse($timeRange->min_time);
            $maxTime = Carbon::parse($timeRange->max_time);
            $elapsedTime = (object)[
                'elapsed_time' => $maxTime->diffInSeconds($minTime),
                'min_time' => $minTime->format('Y-m-d H:i:s'),
                'max_time' => $maxTime->format('Y-m-d H:i:s')
            ];
        }

        // Query 3: Get total count of statements
        $totalCount = DB::table('statements')
            ->where('created_at', '>=', $timeAgo)
            ->where('id', '>', 1)
            ->count();

        return view('admin.database-stats', [
            'statementsPerSecond' => $statementsPerSecond,
            'elapsedTime' => $elapsedTime,
            'totalCount' => (object)['total' => $totalCount],
            'minutes' => $minutes
        ]);
    }

    /**
     * Clean up the database by deleting statements and truncating platform_puids.
     *
     * @return RedirectResponse
     */
    public function cleanup(): RedirectResponse
    {
        try {
            // Start a transaction
            DB::beginTransaction();

            // Delete statements where id > 1
            $deletedCount = DB::table('statements')
                ->where('id', '>', 1)
                ->delete();

            // Truncate platform_puids table
            DB::statement('TRUNCATE TABLE platform_puids');


            return redirect()
                ->route('admin.database-stats')
                ->with('success', "Database cleaned up successfully. Deleted $deletedCount statements and truncated platform_puids table.");

        } catch (\Exception $e) {
            // If an error occurs, rollback the transaction
            DB::rollBack();

            return redirect()
                ->route('admin.database-stats')
                ->with('error', 'Database cleanup failed: ' . $e->getMessage());
        }
    }
}
