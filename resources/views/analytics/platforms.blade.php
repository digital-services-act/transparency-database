@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics"/>
@endsection


@section('content')

    <x-analytics.header />

    <h2 class="ecl-u-type-heading-2">Platform Statements Last {{ $last_days }} Days</h2>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <div id="apexchart"></div>
    <script>
      var options = {
        series: [{
          name: 'statements',
          data: [{{ implode(', ', array_map(function($a){return $a['total'];}, $platform_totals)) }}]
        }],
        chart: {
          type: 'bar',
          height: 500
        },
        plotOptions: {
          bar: {
            borderRadius: 4,
            horizontal: true,
          }
        },
        dataLabels: {
          enabled: false
        },
        xaxis: {
          categories: [{!! "'" . implode("', '", array_map(function($a){return $a['name'];}, $platform_totals)) . "'" !!}],
        }
      };

      var chart = new ApexCharts(document.querySelector('#apexchart'), options)

      chart.render()
    </script>

@endsection
