@extends(backpack_view('blank'))

@php

$widgets['before_content'][] =
[
'type'    => 'div',
'class'   => 'row',
'content' =>
    [ // widgets here
        [
        'type'       => 'chart',
        'controller' => \App\Http\Controllers\Admin\Charts\TopPlatformsChartController::class,

        // OPTIONALS

        'class'   => 'card mb-2',
        'wrapper' => ['class'=> 'col-md-6'] ,
        'content' => [
              'header' => 'Top platforms',
              'body'   => 'This chart shows the 5 most active platforms.<br><br>',
         ],
        ],
        [
        'type'       => 'chart',
        'controller' => \App\Http\Controllers\Admin\Charts\DailyStatementsChartController::class,

        // OPTIONALS

        'class'   => 'card mb-2',
        'wrapper' => ['class'=> 'col-md-6'] ,
        'content' => [
              'header' => 'New Statements',
              'body'   => 'This chart should make it obvious how many new statements have been created in the past 7 days.<br><br>',
         ],
        ]

    ]
];

$widgets['before_content'][] =
[
'type'    => 'div',
'class'   => 'row',
'content' =>
    [ // widgets here
        [
        'type'       => 'chart',
        'controller' => \App\Http\Controllers\Admin\Charts\TopCategoriesChartController::class,

        // OPTIONALS

        'class'   => 'card mb-2',
        'wrapper' => ['class'=> 'col-md-12'] ,
        'content' => [
              'header' => 'Top categories',
              'body'   => 'This chart shows the most popular categories.<br><br>',
         ],
        ]


    ]
]




@endphp

@section('content')
    {{--    <h2>Welcome to the DSA Admin Panel</h2>--}}
@endsection
