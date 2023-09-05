@props(['series' => '', 'values' => [], 'labels' => '', 'height' => 400, 'id' => 'apexchart'])
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
      type: 'line',
      height: {{ $height }},
      zoom: {
        type: 'x',
        enabled: true,
        autoScaleYaxis: true
      },
      // toolbar: {
      //   autoSelected: 'pan'
      // }
    },
    stroke: {
      curve: 'smooth'
    },
    dataLabels: {
      enabled: false
    },
    xaxis: {
      categories: [{!! "'" . implode("', '", $labels) . "'" !!}],
    }
  };

  var chart = new ApexCharts(document.querySelector('#{{ $id }}'), options);

  chart.render();
  // chart.zoomX(1, 20);
</script>