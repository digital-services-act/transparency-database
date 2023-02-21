<?php

namespace App\Http\Controllers;

use Exception;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class ReportsController extends Controller
{
    /**
     * @throws Exception
     */
    public function index()
    {

        $chart_options = [
            'chart_title' => 'Statements by months',
            'report_type' => 'group_by_date',
            'model' => 'App\Models\Statement',
            'group_by_field' => 'date_sent',
            'group_by_period' => 'month',
            'chart_type' => 'bar',
        ];
        $chart = new LaravelChart($chart_options);

        return view('reports.index', [
            'chart' => $chart
        ]);
    }
}