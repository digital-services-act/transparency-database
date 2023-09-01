@props(['data' => false, 'height' => 400])
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<div id="apexchart"></div>
<script>
  var options = {
    colors:['#004494', '#FFD617', '#404040'],
    series: [{
      name: 'statements',
      data: [{{ implode(', ', array_map(function($a){return $a['total'];}, $data)) }}]
    }],
    chart: {
      type: 'bar',
      height: {{ $height }}
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
      categories: [{!! "'" . implode("', '", array_map(function($a){return $a['name'];}, $data)) . "'" !!}],
    }
  };

  var chart = new ApexCharts(document.querySelector('#apexchart'), options)

  chart.render()
</script>