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

    <h2 id="graphs">Your Platform Statements Over Time</h2>

    <ol class="ecl-timeline" data-ecl-auto-init="Timeline" data-ecl-timeline="">
        @foreach($days_count as $days_ago => $count)

        <li class="ecl-timeline__item" id="{{ $days_ago }}">
            <div class="ecl-timeline__tooltip">
                <div class="ecl-timeline__tooltip-arrow"></div>
{{--                @if($loop->first)<div class="ecl-timeline__title">Statements Created</div>@endif--}}
                <div class="ecl-timeline__label">last @if(!$loop->first){{ $days_ago }} days @else day @endif</div>
                <div class="ecl-timeline__content">{{ $count }}</div>
            </div>
        </li>

        @endforeach

    </ol>


    <h3>Statements Created Last {{ $days_ago_max }} Days</h3>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.css">
    <script src="//cdn.jsdelivr.net/chartist.js/latest/chartist.min.js"></script>
    <div class="ct-chart ct-perfect-fourth"></div>
    <script>
      var data = {
        // A labels array that can contain any sort of values
        labels: [{!! $date_labels !!}],
        // Our series array that contains series objects or in this case series data arrays
        series: [
          [{!! $date_counts !!}]
        ]
      };

      var options = {
        seriesBarDistance: 5,
        axisY: {
          onlyInteger: true,
        }
      };

      var responsiveOptions = [
        ['screen and (max-width: 640px)', {
          seriesBarDistance: 5,
          axisX: {
            labelInterpolationFnc: function (value) {
              return value[0];
            }
          }
        }]
      ];


      // Create a new line chart object where as first parameter we pass in a selector
      // that is resolving to our chart container element. The Second parameter
      // is the actual data object.
      new Chartist.Bar('.ct-chart', data, options, responsiveOptions);
    </script>




@endsection
