@props(['platform' => null, 'platform_report' => null, 'days_ago' => 0, 'months_ago' => 0 ])
<h2 class="ecl-u-type-heading-2">Platform: {{ $platform->name }}</h2>

<div class="ecl-fact-figures ecl-fact-figures--col-3">
    <div class="ecl-fact-figures__items">

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="infographic"/>
            </svg>
            <div class="ecl-fact-figures__value">{{ $platform_report['platform_total'] }} statements</div>
            <div class="ecl-fact-figures__title">All Time Total</div>
        </div>

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="growth"/>
            </svg>
            <div class="ecl-fact-figures__value">{{ $platform_report['platform_last_days_ago'] }} statements</div>
            <div class="ecl-fact-figures__title">Last {{ $days_ago }} Days</div>
        </div>

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="growth"/>
            </svg>
            <div class="ecl-fact-figures__value">{{ $platform_report['platform_last_months_ago'] }} statements</div>
            <div class="ecl-fact-figures__title">Last {{ $months_ago }} months</div>
        </div>

    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h2 class="ecl-u-type-heading-2">SORs {{ $platform->name }} created for the Last {{ $days_ago }} Days</h2>
<div id="apechartdays"></div>
<script>
  var options = {
    chart: {
      type: 'line',
    }, colors: ['#004494', '#FFD617', '#404040', '#BFD0E4', '#FFF4BB', '#D9D9D9'], series: [
      {
        name: 'statements', data: [
              {{ implode(',', array_column($platform_report['date_counts'], 'count')) }}],
      }], xaxis: {
      labels: {
        rotate: -45,
      }, categories: [
              {!! implode(',', array_map(function($d, $i){return "'" . $d['date']->format('m-d') . "'";}, $platform_report['date_counts'], array_keys($platform_report['date_counts']))) !!}],
    }, yaxis: {
      title: {
        text: 'Statements',
      },
    },
  }

  var chart = new ApexCharts(document.querySelector('#apechartdays'), options)

  chart.render()
</script>

<h2 class="ecl-u-type-heading-2">SORs {{ $platform->name }} created for the Last {{ $months_ago }} Months</h2>
<div id="apechartmonths"></div>
<script>
  var options = {
    chart: {
      type: 'line',
    }, colors: ['#004494', '#FFD617', '#404040', '#BFD0E4', '#FFF4BB', '#D9D9D9'], series: [
      {
        name: 'statements', data: [
              {{ implode(',', array_column($platform_report['month_counts'], 'count')) }}],
      }], xaxis: {
      labels: {
        rotate: -45,
      }, categories: [
              {!! implode(',', array_map(function($d, $i){return "'" . $d['month']->format('m-Y') . "'";}, $platform_report['month_counts'], array_keys($platform_report['month_counts']))) !!}],
    }, yaxis: {
      title: {
        text: 'Statements',
      },
    },
  }

  var chart = new ApexCharts(document.querySelector('#apechartmonths'), options)

  chart.render()
</script>