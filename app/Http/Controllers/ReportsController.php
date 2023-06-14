<?php

namespace App\Http\Controllers;

use App\Models\Statement;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class ReportsController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(Request $request): Factory|View|Application
    {
        $platform_id = $request->user()->platform->id;

        $days = [
            1,
            7,
            14,
            30,
            60,
            90,
            180,
            360,
            720,
            1080
        ];
        $days_count = [];
        foreach ($days as $days_ago) {
            $days_count[$days_ago] = DB::table('statements')
                ->join('users', 'users.id', '=', 'statements.user_id')
                ->join('platforms', 'platforms.id', '=', 'users.platform_id')
                ->selectRaw('count(statements.id) as statements_count')
                ->where('platforms.id', $platform_id)
                ->where('statements.created_at', '>=', Carbon::now()->subDays($days_ago)->format('Y-m-d 00:00:00'))
                ->get()->first()->statements_count;
        }




        $date_counts = [];

        $days_ago_max = 14;
        $i = 0;

        /** @var Collection $days_result */
        $days_result = DB::table('statements')
          ->join('users', 'users.id', '=', 'statements.user_id')
          ->join('platforms', 'platforms.id', '=', 'users.platform_id')
          ->selectRaw('count(statements.id) as statements_count, DATE(statements.created_at) as created_at_date')
          ->groupByRaw('DATE(statements.created_at)')
          ->where('platforms.id', $platform_id)
          ->where('statements.created_at', '>=', Carbon::now()->subDays($days_ago_max)->format('Y-m-d 00:00:00'))
          ->get();



        while($i < $days_ago_max)
        {
            $d = Carbon::now()->subDays($i)->format('Y-m-d');
            $c = $days_result->firstWhere('created_at_date', $d)->statements_count ?? 0;
            $date_counts[$d] = $c;
            $i++;
        }



        $date_labels = "'" . implode("','", array_keys($date_counts)) . "'";
        $date_counts = implode(',', array_values($date_counts));


        $your_platform_total = DB::table('statements')
                                 ->join('users', 'users.id', '=', 'statements.user_id')
                                 ->join('platforms', 'platforms.id', '=', 'users.platform_id')
                                 ->selectRaw('count(statements.id) as statements_count')
                                 ->where('platforms.id', $platform_id)
                                 ->get()->first()->statements_count;


        $total = Statement::count();

        return view('reports.index', [
            'days_count' => $days_count,
            'total' => $total,
            'your_platform_total' => $your_platform_total,
            'date_labels' => $date_labels,
            'date_counts' => $date_counts,
            'days_ago_max' => $days_ago_max
        ]);
    }
}
