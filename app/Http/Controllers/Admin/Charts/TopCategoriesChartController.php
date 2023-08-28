<?php

namespace App\Http\Controllers\Admin\Charts;

use App\Models\Platform;
use App\Models\Statement;
use Backpack\CRUD\app\Http\Controllers\ChartController;
use ConsoleTVs\Charts\Classes\Highcharts\Chart;
use Illuminate\Support\Facades\DB;

/**
 * Class TopCategoriesChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TopCategoriesChartController extends ChartController
{
    public function setup()
    {
        $this->chart = new Chart();

        $topCategories = Statement::select('category', DB::raw('COUNT(statements.id) as statement_count'))
            ->groupBy('category')
            ->orderByDesc('statement_count')
            ->limit(5)
            ->get();

        $this->chart->dataset('Categories', 'pie', $topCategories->pluck('statement_count'))
            ->color('rgb(77, 189, 116)')
//            ->backgroundColor('rgba(77, 189, 116, 0.4)')
        ;

        $this->chart->labels($topCategories->pluck('category'));

        // RECOMMENDED.
        // Set URL that the ChartJS library should call, to get its data using AJAX.
//        $this->chart->load(backpack_url('charts/top-platforms'));

        // OPTIONAL.
        $this->chart->minimalist(false);
        $this->chart->displayLegend(true);
    }

    /**
     * Respond to AJAX calls with all the chart data points.
     *
     * @return json
     */
    // public function data()
    // {
    //     $users_created_today = \App\User::whereDate('created_at', today())->count();

    //     $this->chart->dataset('Users Created', 'bar', [
    //                 $users_created_today,
    //             ])
    //         ->color('rgba(205, 32, 31, 1)')
    //         ->backgroundColor('rgba(205, 32, 31, 0.4)');
    // }
}
