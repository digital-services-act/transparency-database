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

        $your_platform_total = Statement::whereHas('platform', function(Builder $subquery) use($platform_id) { $subquery->where('platforms.id', $platform_id); })->count();
        $total = Statement::all()->count();

        return view('reports.index', [
            'days_count' => $days_count,
            'total' => $total,
            'your_platform_total' => $your_platform_total
        ]);
    }
}
