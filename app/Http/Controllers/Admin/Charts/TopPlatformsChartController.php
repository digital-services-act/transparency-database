<?php

namespace App\Http\Controllers\Admin\Charts;

use App\Models\Platform;
use App\Models\Statement;
use Backpack\CRUD\app\Http\Controllers\ChartController;
//use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use ConsoleTVs\Charts\Classes\Highcharts\Chart;
use Illuminate\Support\Facades\DB;

/**
 * Class TopPlatformsChartController
 * @package App\Http\Controllers\Admin\Charts
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TopPlatformsChartController extends ChartController
{
    public function setup()
    {
        $this->chart = new Chart();

        $topPlatforms = Platform::select('platforms.id', 'platforms.name', DB::raw('COUNT(statements.id) as statement_count'))
            ->leftJoin('statements', 'platforms.id', '=', 'statements.platform_id')
            ->groupBy('platforms.id', 'platforms.name')
            ->orderByDesc('statement_count')
            ->limit(5)
            ->get();


        $this->chart->dataset('Statements', 'pie', $topPlatforms->pluck('statement_count'))
            ->color('rgb(77, 189, 116)')
//            ->backgroundColor('rgba(77, 189, 116, 0.4)')
        ;

        $this->chart->labels($topPlatforms->pluck('name'));

        // RECOMMENDED.
        // Set URL that the ChartJS library should call, to get its data using AJAX.
//        $this->chart->load(backpack_url('charts/top-platforms'));

        // OPTIONAL.
        $this->chart->minimalist(false);
        $this->chart->displayLegend(true);
    }

//    public function setup()
//    {
//        $this->chart = new Chart();
//
//        $this->chart->dataset('Red', 'pie', [10, 20, 80, 30])
//            ->backgroundColor([
//                'rgb(70, 127, 208)',
//                'rgb(77, 189, 116)',
//                'rgb(96, 92, 168)',
//                'rgb(255, 193, 7)',
//            ]);
//
//        // OPTIONAL
//        $this->chart->displayAxes(false);
//        $this->chart->displayLegend(true);
//
//        // MANDATORY. Set the labels for the dataset points
//        $this->chart->labels(['HTML', 'CSS', 'PHP', 'JS']);
//    }

//    /**
//     * Respond to AJAX calls with all the chart data points.
//     *
//     * @return json
//     */
//    public function data()
//    {
//
//        $topPlatforms = Platform::select('platforms.id', 'platforms.name', DB::raw('COUNT(statements.id) as statement_count'))
//            ->leftJoin('statements', 'platforms.id', '=', 'statements.platform_id')
//            ->groupBy('platforms.id', 'platforms.name')
//            ->orderByDesc('statement_count')
//            ->limit(5)
//            ->get();
//
//
//        $this->chart->dataset('Statements', 'bar', $topPlatforms->pluck('statement_count'))
//            ->color('rgb(77, 189, 116)')
//            ->backgroundColor('rgba(77, 189, 116, 0.4)');
//
////        $this->chart->labels($topPlatforms->pluck('name'));
//
//    }
}
