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
        foreach ($days as $days_ago)
        {
            $days_count[$days_ago] = Statement::whereHas('platform', function(Builder $subquery) use($platform_id) { $subquery->where('platforms.id', $platform_id); })->where('created_at', '>=', Carbon::now()->subDays($days_ago))->count();
        }

        //dd($days_count);


        $date_labels = [];
        $date_counts = [];

        $days_ago_max = 14;
        $i = 0;
        while($i < $days_ago_max)
        {
            $date_counts[] = Statement::whereHas( 'platform',
                function(Builder $subquery) use($platform_id) {
                    $subquery->where('platforms.id', $platform_id);
                }
            )->where('created_at', '>=', Carbon::now()->subDays($i))
                              ->where('created_at', '<', Carbon::now()->subDays($i-1))
                              ->count();


            $date_labels[] = $i % 2 == 0 ? Carbon::now()->subDays($i)->format('d-m-Y') : '';
            $i++;
        }


        $date_labels = "'" . implode("','", $date_labels) . "'";
        $date_counts = implode(',', $date_counts);


        $your_platform_total = Statement::whereHas('platform', function(Builder $subquery) use($platform_id) { $subquery->where('platforms.id', $platform_id); })->count();
        $total = Statement::all()->count();

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
