@props(['keyword_report' => null, 'days_ago' => 0, 'months_ago' => 0 ])
<div class="ecl-fact-figures ecl-fact-figures--col-3">
    <div class="ecl-fact-figures__items">

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="data"/>
            </svg>
            <div class="ecl-fact-figures__value">{{ $keyword_report['keyword_total'] }} statements of reasons</div>
            <div class="ecl-fact-figures__title">All Time Total</div>
        </div>

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="growth"/>
            </svg>
            <div class="ecl-fact-figures__value">{{ $keyword_report['keyword_last_days_ago'] }} statements of reasons</div>
            <div class="ecl-fact-figures__title">Last {{ $days_ago }} Days</div>
        </div>

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="infographic"/>
            </svg>
            <div class="ecl-fact-figures__value">{{ $keyword_report['keyword_last_months_ago'] }} statements of reasons</div>
            <div class="ecl-fact-figures__title">Last {{ $months_ago }} months</div>
        </div>

    </div>
</div>

<h2 class="ecl-u-type-heading-2">Created for the Last {{ count($keyword_report['day_totals_values']) }} Days</h2>

<x-analytics.line-chart :values="$keyword_report['day_totals_values']" :labels="$keyword_report['day_totals_labels']" height="400" id="apexkeyworddays"/>

<h2 class="ecl-u-type-heading-2">Created for the Last {{ count($keyword_report['month_totals_values']) }} {{Str::of('Month')->plural(count($keyword_report['month_totals_values']))}}</h2>

<x-analytics.line-chart :values="$keyword_report['month_totals_values']" :labels="$keyword_report['month_totals_labels']" height="400" id="apexkeywordonths"/>
