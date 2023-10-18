@props(['category' => null, 'category_report' => null, 'days_ago' => 0, 'months_ago' => 0 ])
<div class="ecl-fact-figures ecl-fact-figures--col-3">
    <div class="ecl-fact-figures__items">

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="data"/>
            </svg>
            <div class="ecl-fact-figures__value">@aif($category_report['category_total']) statements of reasons</div>
            <div class="ecl-fact-figures__title">All Time Total</div>
        </div>

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="growth"/>
            </svg>
            <div class="ecl-fact-figures__value">@aif($category_report['category_last_days_ago']) statements of reasons</div>
            <div class="ecl-fact-figures__title">Last @aif($days_ago) Days</div>
        </div>

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="infographic"/>
            </svg>
            <div class="ecl-fact-figures__value">@aif($category_report['category_last_months_ago']) statements of reasons</div>
            <div class="ecl-fact-figures__title">Last @aif($months_ago) months</div>
        </div>

    </div>
</div>

{{--<div class="ecl-row">--}}
{{--    @isset($category_report['top_platforms'])--}}
{{--        <div class="ecl-col-6">--}}
{{--            <h3 class="ecl-u-type-heading-3">Most Reporting Platforms</h3>--}}
{{--            <ul class="ecl-unordered-list">--}}
{{--                @foreach($category_report['top_platforms'] as $top_platform)--}}
{{--                    <li class="ecl-unordered-list__item">--}}
{{--                        <a href="{{ route('analytics.platform-category', ['uuid' => $top_platform->uuid, 'category' => $category]) }}"--}}
{{--                           class="ecl-link--standalone">{{ $top_platform->name }}</a>--}}
{{--                    </li>--}}
{{--                @endforeach--}}
{{--            </ul>--}}
{{--        </div>--}}
{{--    @endif--}}
{{--</div>--}}

<h2 class="ecl-u-type-heading-2">Created for the Last @aif(count($category_report['day_totals_values'])) Days</h2>

<x-analytics.line-chart :values="$category_report['day_totals_values']" :labels="$category_report['day_totals_labels']" height="400" id="apexcategorydays"/>

<h2 class="ecl-u-type-heading-2">Created for the Last @aif(count($category_report['month_totals_values'])) {{Str::of('Month')->plural(count($category_report['month_totals_values']))}}</h2>

<x-analytics.line-chart :values="$category_report['month_totals_values']" :labels="$category_report['month_totals_labels']" height="400" id="apexcategoryonths"/>
