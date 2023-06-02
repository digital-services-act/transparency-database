<?php

namespace App\Http\Controllers;

use App\Models\Statement;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class ReportsController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(Request $request): Factory|View|Application
    {

        $user_id = auth()->user()->id;
        $own = 'user_id = ' . $user_id;

        $days = 30;
        $days_count = Statement::where('user_id', $user_id)->where('created_at', '>=', Carbon::now()->subDays($days))->count();

        $your_count = Statement::where('user_id', $user_id)->count();

        $total = Statement::all()->count();
        $total_twentyfour = Statement::where('created_at', '>=', Carbon::now()->subDays(1))->count();

        $title         = "Statements Created";
        $chart_options = [
            'chart_title'     => $title,
            'report_type'     => 'group_by_date',
            'model'           => 'App\Models\Statement',
            'group_by_field'  => 'created_at',
            'group_by_period' => 'month',
            'chart_type'      => 'bar',
            'where_raw'       => $own,
        ];
        $chart         = new LaravelChart($chart_options);


        if ($request->get('chart') == 'statements-by-source') {
            $title = "Statements by Source";
            $sources = Statement::SOURCES;
            $chart_options = [];
            $colors = ['SOURCE_ARTICLE_16' => 'black', 'SOURCE_VOLUNTARY' => 'blue'];
            foreach ($sources as $key => $source) {
                $chart_options[] = [
                    'chart_title' => $source,
                    'chart_type' => 'line',
                    'where_raw'  => $own,
                    'conditions'            => [
                        ['name' => $source, 'condition' => 'source = \''.$key.'\'', 'color' => $colors[$key], 'fill' => true]
                    ],
                    'report_type' => 'group_by_date',
                    'model' => 'App\Models\Statement',
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'month',
                ];
            }
            $chart = new LaravelChart($chart_options[0], $chart_options[1]);
        }



        if ($request->get('chart') == 'statements-by-country') {
            $title = "Statements by Country";
            $countries = Statement::EUROPEAN_COUNTRY_CODES;
            $chart_options = [];
            $colors = [
                'AT' => 'red',
                'BE' => 'orange',
                'BG' => 'yellow',
                'CY' => 'green',
                'CZ' => 'blue',
                'DE' => 'indigo',
                'DK' => 'violet',
                'EE' => 'black',
                'ES' => 'red',
                'FI' => 'orange',
                'FR' => 'yellow',
                'GR' => 'green',
                'HR' => 'blue',
                'HU' => 'indigo',
                'IE' => 'violet',
                'IT' => 'black',
                'LT' => 'red',
                'LU' => 'orange',
                'LV' => 'yellow',
                'MT' => 'green',
                'NL' => 'blue',
                'PL' => 'indigo',
                'PT' => 'violet',
                'RO' => 'black',
                'SE' => 'red',
                'SI' => 'orange',
                'SK' => 'yellow'
            ];
            foreach ($countries as $key => $country) {
                $chart_options[] = [
                    'chart_title' => $country,
                    'chart_type' => 'line',
                    'where_raw'  => $own,
                    'conditions'            => [
                        ['name' => $country, 'condition' => 'countries_list LIKE \'%'.$country.'%\'', 'color' => $colors[$country], 'fill' => true]
                    ],
                    'report_type' => 'group_by_date',
                    'model' => 'App\Models\Statement',
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'month',
                    'hidden' => true,
                ];
            }
            $chart = new LaravelChart(
                $chart_options[0],
                $chart_options[1],
                $chart_options[2],
                $chart_options[3],
                $chart_options[4],
                $chart_options[5],
                $chart_options[6],
                $chart_options[7],
                $chart_options[8],
                $chart_options[9],
                $chart_options[10],
                $chart_options[11],
                $chart_options[12],
                $chart_options[13],
                $chart_options[14],
                $chart_options[15],
                $chart_options[16],
                $chart_options[17],
                $chart_options[18],
                $chart_options[19],
                $chart_options[20],
                $chart_options[21],
                $chart_options[22],
                $chart_options[23],
                $chart_options[24],
                $chart_options[25],
                $chart_options[26],
            );
        }

//        if ($request->get('chart') == 'statements-by-decision') {
//            $title = "Statements by Decisions";
//            $decisions = Statement::DECISIONS;
//            $chart_options = [];
//            $colors = ['DECISION_ALL' => 'black', 'DECISION_MONETARY' => 'blue', 'DECISION_PROVISION' => 'red', 'DECISION_TERMINATION' => 'yellow'];
//            foreach ($decisions as $key => $decision) {
//                $decision = str_replace("'", "", $decision); // Meh, charts can't handle the '
//                $chart_options[] = [
//                    'chart_title' => $decision,
//                    'chart_type' => 'line',
//                    'where_raw'  => $own,
//                    'conditions'            => [
//                        ['name' => $decision, 'condition' => 'decision_taken = \''.$key.'\'', 'color' => $colors[$key], 'fill' => true]
//                    ],
//                    'report_type' => 'group_by_date',
//                    'model' => 'App\Models\Statement',
//                    'group_by_field' => 'created_at',
//                    'group_by_period' => 'month',
//                ];
//            }
//            $chart = new LaravelChart($chart_options[0], $chart_options[1], $chart_options[2], $chart_options[3]);
//        }

        if ($request->get('chart') == 'statements-by-ground') {
            $title = "Statements by Ground";
            $grounds = Statement::DECISION_GROUNDS;
            $chart_options = [];
            $colors = ['ILLEGAL_CONTENT' => 'black', 'INCOMPATIBLE_CONTENT' => 'blue'];
            foreach ($grounds as $key => $ground) {
                $chart_options[] = [
                    'chart_title' => $ground,
                    'chart_type' => 'line',
                    'where_raw'  => $own,
                    'conditions'            => [
                        ['name' => $ground, 'condition' => 'decision_ground = \''.$key.'\'', 'color' => $colors[$key], 'fill' => true]
                    ],
                    'report_type' => 'group_by_date',
                    'model' => 'App\Models\Statement',
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'month',
                ];
            }
            $chart = new LaravelChart($chart_options[0], $chart_options[1]);
        }

        if ($request->get('chart') == 'statements-by-automatic-detection') {
            $title = "Statements by Automatic Detection";
            $detections = Statement::AUTOMATED_DETECTIONS;
            $chart_options = [];
            $colors = ['Yes' => 'black', 'No' => 'blue'];
            foreach ($detections as $key => $detection) {
                $chart_options[] = [
                    'chart_title' => $detection,
                    'chart_type' => 'line',
                    'where_raw'  => $own,
                    'conditions'            => [
                        ['name' => $detection, 'condition' => 'automated_detection = \''.$detection.'\'', 'color' => $colors[$detection], 'fill' => true]
                    ],
                    'report_type' => 'group_by_date',
                    'model' => 'App\Models\Statement',
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'month',
                ];
            }
            $chart = new LaravelChart($chart_options[0], $chart_options[1]);
        }

        if ($request->get('chart') == 'statements-by-method') {
            $title = "Statements by Method";
            $chart_options1 = [
                'chart_title' => 'Statements Created by Form',
                'chart_type' => 'line',
                'where_raw'  => $own,
                'conditions'            => [
                    ['name' => 'Form', 'condition' => 'method = \'FORM\'', 'color' => 'black', 'fill' => true],
//                    ['name' => 'API', 'condition' => 'method = \'API\'', 'color' => 'blue', 'fill' => true],

                ],
                'report_type' => 'group_by_date',
                'model' => 'App\Models\Statement',
                'group_by_field' => 'created_at',
                'group_by_period' => 'month',
            ];
            $chart_options2 = [
                'chart_title' => 'Statements Created by API',
                'chart_type' => 'line',
                'where_raw'  => $own,
                'conditions'            => [
//                    ['name' => 'Form', 'condition' => 'method = \'FORM\'', 'color' => 'black', 'fill' => true],
                    ['name' => 'API', 'condition' => 'method = \'API\'', 'color' => 'blue', 'fill' => true]
                ],
                'report_type' => 'group_by_date',
                'model' => 'App\Models\Statement',
                'group_by_field' => 'created_at',
                'group_by_period' => 'month',
            ];

            $chart = new LaravelChart($chart_options1, $chart_options2);
        }

        return view('reports.index', [
            'chart' => $chart,
            'title' => $title,
            'days' => $days,
            'days_count' => $days_count,
            'total' => $total,
            'total_twentyfour' => $total_twentyfour,
            'your_count' => $your_count
        ]);
    }
}
