@php use App\Models\Statement; @endphp
@props(['platform' => null, 'platform_report' => null, 'days_ago' => 0, 'months_ago' => 0 ])
<div class="ecl-fact-figures ecl-fact-figures--col-3">
    <div class="ecl-fact-figures__items">

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="data"/>
            </svg>
            <div class="ecl-fact-figures__value">@aif($platform_report['platform_total']) statements of reasons</div>
            <div class="ecl-fact-figures__title">All Time Total</div>
        </div>

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="growth"/>
            </svg>
            <div class="ecl-fact-figures__value">@aif($platform_report['platform_last_days_ago']) statements of
                reasons
            </div>
            <div class="ecl-fact-figures__title">Last @aif($days_ago) Days</div>
        </div>

        <div class="ecl-fact-figures__item">
            <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                <x-ecl.icon icon="infographic"/>
            </svg>
            <div class="ecl-fact-figures__value">@aif($platform_report['platform_last_months_ago']) statements of
                reasons
            </div>
            <div class="ecl-fact-figures__title">Last @aif($months_ago) months</div>
        </div>

    </div>
</div>

{{--<div class="ecl-row">--}}
{{--    <div class="ecl-col-6">--}}
{{--        <h3 class="ecl-u-type-heading-3">Most Used Categories</h3>--}}
{{--        <ul class="ecl-unordered-list">--}}
{{--            @foreach($platform_report['top_categories'] as $top_category)--}}
{{--                <li class="ecl-unordered-list__item">--}}
{{--                    <a href="{{ route('analytics.platform-category', ['uuid' => $platform->uuid, 'category' => $top_category->value]) }}"--}}
{{--                       class="ecl-link--standalone">{{ Statement::STATEMENT_CATEGORIES[$top_category->value] }}</a>--}}
{{--                </li>--}}
{{--            @endforeach--}}
{{--        </ul>--}}
{{--    </div>--}}
{{--</div>--}}

<h2 class="ecl-u-type-heading-2">Created for the Last @aif(count($platform_report['day_totals_values'])) Days</h2>

<x-analytics.line-chart :values="$platform_report['day_totals_values']" :labels="$platform_report['day_totals_labels']"
                        height="400" id="apexplatformdays"/>

<h2 class="ecl-u-type-heading-2">Created for the
    Last @aif(count($platform_report['month_totals_values'])) {{Str::of('Month')->plural(count($platform_report['month_totals_values']))}}</h2>

<x-analytics.line-chart :values="$platform_report['month_totals_values']"
                        :labels="$platform_report['month_totals_labels']" height="400" id="apexplatformonths"/>

