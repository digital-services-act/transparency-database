@props(['platform' => null, 'platform_report' => null, 'days_ago' => 0, 'months_ago' => 0 ])
<div class="ecl-fact-figures ecl-fact-figures--col-3">
    <div class="ecl-fact-figures__items">

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="data"/>
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
                <x-ecl.icon icon="infographic"/>
            </svg>
            <div class="ecl-fact-figures__value">{{ $platform_report['platform_last_months_ago'] }} statements</div>
            <div class="ecl-fact-figures__title">Last {{ $months_ago }} months</div>
        </div>

    </div>
</div>

<h2 class="ecl-u-type-heading-2">SORs {{ $platform->name }} created for the Last {{ $days_ago }} Days</h2>

<x-analytics.line-chart :values="$platform_report['day_totals_values']" :labels="$platform_report['day_totals_labels']" height="400" id="apexplatformdays"/>

<h2 class="ecl-u-type-heading-2">SORs {{ $platform->name }} created for the Last {{ $months_ago }} Months</h2>

<x-analytics.line-chart :values="$platform_report['month_totals_values']" :labels="$platform_report['month_totals_labels']" height="400" id="apexplatformonths"/>
