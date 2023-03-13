<?php

namespace App\Http\Controllers;

use App\Models\Statement;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class ReportsController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(Request $request): Factory|View|Application
    {


        $title         = "Statements Created";
        $chart_options = [
            'chart_title'     => $title,
            'report_type'     => 'group_by_date',
            'model'           => 'App\Models\Statement',
            'group_by_field'  => 'created_at',
            'group_by_period' => 'month',
            'chart_type'      => 'bar',
        ];
        $chart         = new LaravelChart($chart_options);


        if ($request->get('chart') == 'statements-by-source') {
            $title = "Statements by Source";
            $sources = Statement::SOURCES;
            $chart_options = [];
            $colors = ['SOURCE_ARTICLE_16' => 'black', 'SOURCE_VOLUNTARY' => 'blue', 'SOURCE_OTHER' => 'red'];
            foreach ($sources as $key => $source) {
                $chart_options[] = [
                    'chart_title' => $source,
                    'chart_type' => 'line',
                    'conditions'            => [
                        ['name' => $source, 'condition' => 'source = \''.$key.'\'', 'color' => $colors[$key], 'fill' => true]
                    ],
                    'report_type' => 'group_by_date',
                    'model' => 'App\Models\Statement',
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'month',
                ];
            }
            $chart = new LaravelChart($chart_options[0], $chart_options[1], $chart_options[2]);
        }

        if ($request->get('chart') == 'statements-by-redress') {
            $title = "Statements by Redress";
            $sources = Statement::REDRESSES;
            $chart_options = [];
            $colors = ['REDRESS_INTERNAL_MECHANISM' => 'black', 'REDRESS_OUT_OF_COURT' => 'blue', 'REDRESS_JUDICIAL' => 'red', 'REDRESS_OTHER' => 'yellow'];
            foreach ($sources as $key => $source) {
                $chart_options[] = [
                    'chart_title' => $source,
                    'chart_type' => 'line',
                    'conditions'            => [
                        ['name' => $source, 'condition' => 'redress = \''.$key.'\'', 'color' => $colors[$key], 'fill' => true]
                    ],
                    'report_type' => 'group_by_date',
                    'model' => 'App\Models\Statement',
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'month',
                ];
            }
            $chart = new LaravelChart($chart_options[0], $chart_options[1], $chart_options[2], $chart_options[3]);
        }

        if ($request->get('chart') == 'statements-by-method') {
            $title = "Statements by Method";
            $chart_options1 = [
                'chart_title' => 'Statements Created by Form',
                'chart_type' => 'line',
                'conditions'            => [
                    ['name' => 'Form', 'condition' => 'method = \'FORM\'', 'color' => 'black', 'fill' => true],
//                    ['name' => 'API', 'condition' => 'method = \'API\'', 'color' => 'blue', 'fill' => true],
//                    ['name' => 'eDelivery', 'condition' => 'method = \'EDELIVERY\'', 'color' => 'red', 'fill' => true],
                ],
                'report_type' => 'group_by_date',
                'model' => 'App\Models\Statement',
                'group_by_field' => 'created_at',
                'group_by_period' => 'month',
            ];
            $chart_options2 = [
                'chart_title' => 'Statements Created by API',
                'chart_type' => 'line',
                'conditions'            => [
//                    ['name' => 'Form', 'condition' => 'method = \'FORM\'', 'color' => 'black', 'fill' => true],
                    ['name' => 'API', 'condition' => 'method = \'API\'', 'color' => 'blue', 'fill' => true],
//                    ['name' => 'eDelivery', 'condition' => 'method = \'EDELIVERY\'', 'color' => 'red', 'fill' => true],
                ],
                'report_type' => 'group_by_date',
                'model' => 'App\Models\Statement',
                'group_by_field' => 'created_at',
                'group_by_period' => 'month',
            ];
            $chart_options3 = [
                'chart_title' => 'Statements Created by eDelivery',
                'chart_type' => 'line',
                'conditions'            => [
//                    ['name' => 'Form', 'condition' => 'method = \'FORM\'', 'color' => 'black', 'fill' => true],
//                    ['name' => 'API', 'condition' => 'method = \'API\'', 'color' => 'blue', 'fill' => true],
                    ['name' => 'eDelivery', 'condition' => 'method = \'EDELIVERY\'', 'color' => 'red', 'fill' => true],
                ],
                'report_type' => 'group_by_date',
                'model' => 'App\Models\Statement',
                'group_by_field' => 'created_at',
                'group_by_period' => 'month',
            ];
            $chart = new LaravelChart($chart_options1, $chart_options2, $chart_options3);
        }

        return view('reports.index', [
            'chart' => $chart,
            'title' => $title,
        ]);
    }
}