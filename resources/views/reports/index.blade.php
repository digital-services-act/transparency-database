@extends('layouts/ecl')

@section('title', 'Reports')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Reports" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Reports and Statistics</h1>


    <div class="ecl-fact-figures ecl-fact-figures--col-2">
        <div class="ecl-fact-figures__items">

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="infographic" />
                </svg>
                <div class="ecl-fact-figures__value">{{ $total }} statements</div>
                <div class="ecl-fact-figures__title">Global Total</div>
                <div class="ecl-fact-figures__description">
                    Everyday very large online platforms are displaying their transparency by uploading their
                    statements of reason to the database.
                </div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="growth" />
                </svg>
                <div class="ecl-fact-figures__value">{{ $your_platform_total }} statements</div>
                <div class="ecl-fact-figures__title">Your Platform Total</div>
                <div class="ecl-fact-figures__description">
                    Every statement your platform created in the database is counted and totalled here.
                </div>
            </div>

        </div>
    </div>


    <div id="apechart"></div>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
      var options = {
        chart: {
          type: "line"
        },
        colors:['#004494', '#FFD617', '#404040', '#BFD0E4', '#FFF4BB', '#D9D9D9'],
        series: [
          {
            name: 'statements',
            data: [
              {{ implode(',', array_column($date_counts, 'count')) }}
            ]
          },
          {{--{--}}
          {{--  name: 'statementsii',--}}
          {{--  data: [--}}
          {{--      {!! implode(',', array_map(function($d){return rand(0,8) + $d['count'];}, $date_counts)) !!}--}}
          {{--  ]--}}
          {{--},--}}
          {{--{--}}
          {{--  name: 'statementsiii',--}}
          {{--  data: [--}}
          {{--      {!! implode(',', array_map(function($d){return rand(0,8) + $d['count'];}, $date_counts)) !!}--}}
          {{--  ]--}}
          {{--},--}}
          {{--{--}}
          {{--  name: 'statementsiv',--}}
          {{--  data: [--}}
          {{--      {!! implode(',', array_map(function($d){return rand(0,8) + $d['count'];}, $date_counts)) !!}--}}
          {{--  ]--}}
          {{--},--}}
          {{--{--}}
          {{--  name: 'statementsv',--}}
          {{--  data: [--}}
          {{--      {!! implode(',', array_map(function($d){return rand(0,8) + $d['count'];}, $date_counts)) !!}--}}
          {{--  ]--}}
          {{--},--}}
          {{--{--}}
          {{--  name: 'statementsvi',--}}
          {{--  data: [--}}
          {{--      {!! implode(',', array_map(function($d){return rand(0,8) + $d['count'];}, $date_counts)) !!}--}}
          {{--  ]--}}
          {{--},--}}

        ],
        xaxis: {
          labels: {
            rotate: -45
          },
          categories: [
            {!! implode(',', array_map(function($d, $i){return "'" . $d['date']->format('m-d') . "'";}, $date_counts, array_keys($date_counts))) !!}
          ]
        },
        yaxis: {
          title: {
            text: "Statements"
          }
        },
      }

      var chart = new ApexCharts(document.querySelector("#apechart"), options);

      chart.render();
    </script>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Date', 'Statements'],
          @foreach($date_counts as $date_count)
          ['{{ $date_count['date']->format('Y-m-d') }}',  {{ $date_count['count'] }}],
          @endforeach
        ]);

        var options = {
          title: 'Your Platform Statements Created for the Last {{ $start_days_ago }} Days',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart'));

        chart.draw(data, options);
      }
    </script>
    <div id="chart" style="width: 900px; height: 500px"></div>

    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawCChart);

      function drawCChart() {
        var data = google.visualization.arrayToDataTable([
          ['Date', 'Statements'],
            @foreach($date_counts as $date_count)
              ['{{ $date_count['date']->format('Y-m-d') }}',  {{ $date_count['count'] }}],
            @endforeach
        ]);

        var options = {
          title: 'Your Platform Statements Created for the Last {{ $start_days_ago }} Days',
          curveType: 'function',
          legend: { position: 'bottom' }
        };

        var chart = new google.visualization.LineChart(document.getElementById('cchart'));

        chart.draw(data, options);
      }
    </script>
    <div id="cchart" style="width: 900px; height: 500px"></div>

    <script>


      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(drawBasic);

      function drawBasic() {
        var data = google.visualization.arrayToDataTable([
          ['Date', 'Statements'],
            @foreach($date_counts as $date_count)
              ['{{ $date_count['date']->format('Y-m-d') }}',  {{ $date_count['count'] }}],
            @endforeach

        ]);

        var options = {
          title: 'Your Platform Statements Created for the Last {{ $start_days_ago }} Days',
          chartArea: {width: '50%'},
          hAxis: {
            title: 'Date',
            minValue: 0
          },
          vAxis: {
            title: 'Statements'
          },
          orientation: 'vertical'
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('bchart'));
        chart.draw(data, options);
      }
    </script>
    <div id="bchart" style="width: 900px; height: 500px"></div>



    <h2 class="ecl-u-type-heading-2">Your Platform Statements Created for the Last {{ $start_days_ago }} Days</h2>

    <ul class="ecl-unordered-list" style="list-style-type: none; rotate: -90deg; position: relative; top: -250px; left: -220px; padding-bottom: 200px;">
        @foreach($date_counts as $date_count)
            <li class="ecl-unordered-list__item">
                <span style="width: 100px; display: inline-block;">{{ $date_count['date']->format('Y-m-d') }}</span>
                <span style="width: 500px; display: inline-block;">
                    <span style="background-color: #004494; color: white; text-align: center; width: {{ $date_count['percentage'] }}%; display: inline-block;">
                        <span style="rotate: 90deg; display: block;">{{ $date_count['count'] }}</span>
                    </span>
                </span>
            </li>
        @endforeach
    </ul>

    <h2 class="ecl-u-type-heading-2">Your Platform Statements Created for the Last {{ $start_days_ago }} Days</h2>

    <ul class="ecl-unordered-list" style="list-style-type: none;">
        @foreach($date_counts as $date_count)
            <li class="ecl-unordered-list__item">
                <span style="width: 100px; display: inline-block;">{{ $date_count['date']->format('Y-m-d') }}</span>
                <span style="width: 500px; display: inline-block;">
                    <span style="background-color: #004494; color: white; text-align: center; width: {{ $date_count['percentage'] }}%; display: inline-block;">
                        <span>{{ $date_count['count'] }}</span>
                    </span>
                </span>
            </li>
        @endforeach
    </ul>




    <h2 class="ecl-u-type-heading-2" id="graphs">Your Platform Statements Created in the ...</h2>

    <ol class="ecl-timeline" data-ecl-auto-init="Timeline" data-ecl-timeline="">
        @foreach($days_count as $days_ago => $count)

            <li class="ecl-timeline__item" id="{{ $days_ago }}">
                <div class="ecl-timeline__tooltip">
                    <div class="ecl-timeline__tooltip-arrow"></div>
                    <div class="ecl-timeline__label">last @if(!$loop->first){{ $days_ago }} days @else day @endif</div>
                    <div class="ecl-timeline__content">{{ $count }}</div>
                </div>
            </li>

        @endforeach

    </ol>





@endsection
