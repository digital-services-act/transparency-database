@extends('layouts/ecl')

@section('title', 'Database Velocity')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Database Velocity" />
@endsection

@section('content')

    <h1 class="ecl-u-type-heading-1">Database Velocity</h1>

    @if($velocities->isEmpty())
        <p class="ecl-u-type-paragraph">No velocity data recorded yet. Data is collected every minute.</p>
    @else
        <div class="ecl-u-mb-l">
            <h2 class="ecl-u-type-heading-2">Rows per Second (Last Hour)</h2>
            <canvas id="rpsChart" height="80"></canvas>
        </div>

        <div class="ecl-u-mb-l">
            <h2 class="ecl-u-type-heading-2">Max Statement ID (Last Hour)</h2>
            <canvas id="maxIdChart" height="80"></canvas>
        </div>

        <h2 class="ecl-u-type-heading-2">Raw Data</h2>
        <table class="ecl-table ecl-table--zebra">
            <thead>
                <tr class="ecl-table__row">
                    <th class="ecl-table__header">Time</th>
                    <th class="ecl-table__header">Max Statement ID</th>
                    <th class="ecl-table__header">Rows / Second</th>
                </tr>
            </thead>
            <tbody>
                @foreach($velocities->reverse() as $velocity)
                    <tr class="ecl-table__row">
                        <td class="ecl-table__cell" data-ecl-table-header="Time">{{ $velocity->created_at->format('H:i:s') }}</td>
                        <td class="ecl-table__cell" data-ecl-table-header="Max Statement ID">{{ number_format($velocity->max_statement_id) }}</td>
                        <td class="ecl-table__cell" data-ecl-table-header="Rows / Second">{{ $velocity->rows_per_second }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const velocities = @json($velocities);
            if (!velocities.length) return;

            const labels = velocities.map(v => {
                const d = new Date(v.created_at);
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            });
            const rpsData = velocities.map(v => parseFloat(v.rows_per_second));
            const maxIdData = velocities.map(v => v.max_statement_id);

            function drawChart(canvasId, data, label, color) {
                const canvas = document.getElementById(canvasId);
                const ctx = canvas.getContext('2d');
                const dpr = window.devicePixelRatio || 1;
                const rect = canvas.getBoundingClientRect();

                canvas.width = rect.width * dpr;
                canvas.height = rect.height * dpr;
                ctx.scale(dpr, dpr);

                const w = rect.width;
                const h = rect.height;
                const padding = { top: 20, right: 20, bottom: 40, left: 80 };
                const chartW = w - padding.left - padding.right;
                const chartH = h - padding.top - padding.bottom;

                const minVal = Math.min(...data);
                const maxVal = Math.max(...data);
                const range = maxVal - minVal || 1;

                ctx.fillStyle = '#f5f5f5';
                ctx.fillRect(padding.left, padding.top, chartW, chartH);

                // Grid lines
                ctx.strokeStyle = '#ddd';
                ctx.lineWidth = 1;
                for (let i = 0; i <= 4; i++) {
                    const y = padding.top + chartH - (i / 4) * chartH;
                    ctx.beginPath();
                    ctx.moveTo(padding.left, y);
                    ctx.lineTo(padding.left + chartW, y);
                    ctx.stroke();

                    const val = minVal + (i / 4) * range;
                    ctx.fillStyle = '#666';
                    ctx.font = '11px sans-serif';
                    ctx.textAlign = 'right';
                    ctx.fillText(val >= 1000 ? (val / 1000).toFixed(1) + 'k' : val.toFixed(1), padding.left - 8, y + 4);
                }

                // Area fill
                ctx.beginPath();
                data.forEach((val, i) => {
                    const x = padding.left + (i / (data.length - 1)) * chartW;
                    const y = padding.top + chartH - ((val - minVal) / range) * chartH;
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                });
                ctx.lineTo(padding.left + chartW, padding.top + chartH);
                ctx.lineTo(padding.left, padding.top + chartH);
                ctx.closePath();
                ctx.fillStyle = color + '33';
                ctx.fill();

                // Line
                ctx.beginPath();
                data.forEach((val, i) => {
                    const x = padding.left + (i / (data.length - 1)) * chartW;
                    const y = padding.top + chartH - ((val - minVal) / range) * chartH;
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                });
                ctx.strokeStyle = color;
                ctx.lineWidth = 2;
                ctx.stroke();

                // X-axis labels
                ctx.fillStyle = '#666';
                ctx.font = '11px sans-serif';
                ctx.textAlign = 'center';
                const step = Math.max(1, Math.floor(labels.length / 6));
                labels.forEach((lbl, i) => {
                    if (i % step === 0 || i === labels.length - 1) {
                        const x = padding.left + (i / (data.length - 1)) * chartW;
                        ctx.fillText(lbl, x, padding.top + chartH + 20);
                    }
                });
            }

            drawChart('rpsChart', rpsData, 'Rows/s', '#0e47cb');
            drawChart('maxIdChart', maxIdData, 'Max ID', '#467a39');
        });
    </script>

@endsection
