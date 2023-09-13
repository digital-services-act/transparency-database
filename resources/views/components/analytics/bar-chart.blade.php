@props(['series' => 'statements', 'values' => [], 'labels' => [], 'height' => 400, 'id' => 'apexchart'])
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<div id="{{ $id }}"></div>
<script>
  var options = {
    colors:['#004494', '#FFD617', '#404040'],
    series: [{
      name: '{{ $series }}',
      data: [{{ implode(', ', $values) }}]
    }],
    chart: {
      type: 'bar',
      height: {{ $height }}
    },
    plotOptions: {
      bar: {
        horizontal: true,
      }
    },
    dataLabels: {
      enabled: false
    },
    xaxis: {
      categories: [{!! "'" . implode("', '", $labels) . "'" !!}],
    }
  };

  var chart = new ApexCharts(document.querySelector('#{{ $id }}'), options)

  chart.render()
</script>