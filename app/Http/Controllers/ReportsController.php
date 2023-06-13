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
                ->where('statements.created_at', '>=', Carbon::now()->subDays($days_ago))
                ->get()->first()->statements_count;
        }



        $date_labels = [];
        $date_counts = [];

        $days_ago_max = 14;
        $i = 0;
        while($i < $days_ago_max)
        {
            $date_count = DB::table('statements')
                               ->join('users', 'users.id', '=', 'statements.user_id')
                               ->join('platforms', 'platforms.id', '=', 'users.platform_id')
                               ->selectRaw('count(statements.id) as statements_count')
                               ->where('platforms.id', $platform_id)
                               ->where('statements.created_at', '>=', Carbon::now()->subDays($i))
                               ->where('statements.created_at', '<', Carbon::now()->subDays($i-1))
                               ->get()->first()->statements_count;


            $date_counts[] = $date_count;
            $date_labels[] = $i % 2 == 0 ? Carbon::now()->subDays($i)->format('d-m-Y') : '';
            $i++;
        }



        $date_labels = "'" . implode("','", $date_labels) . "'";
        $date_counts = implode(',', $date_counts);


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
