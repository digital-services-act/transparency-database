@extends('layouts/ecl')

@section('title', 'Reports')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Reports" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Reports</h1>


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

    <h2 class="ecl-u-type-heading-2">Your Platform Statements Created for the Last {{ $start_days_ago }} Days</h2>
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

@endsection
