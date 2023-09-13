@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics"/>
@endsection


@section('content')

    <x-analytics.header/>

    <h2 class="ecl-u-type-heading-2">Overview</h2>

    <div class="ecl-fact-figures ecl-fact-figures--col-3">
        <div class="ecl-fact-figures__items">

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="data"/>
                </svg>
                <div class="ecl-fact-figures__value">{{ $total }}</div>
                <div class="ecl-fact-figures__title">Statements All Time Total</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="data"/>
                </svg>
                <div class="ecl-fact-figures__value">{{ $total_last_days }}</div>
                <div class="ecl-fact-figures__title">Statements last {{ $last_days }} Days</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="data"/>
                </svg>
                <div class="ecl-fact-figures__value">{{ $total_last_months }}</div>
                <div class="ecl-fact-figures__title">Statements last {{ $last_months }} months</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="growth"/>
                </svg>
                <div class="ecl-fact-figures__value">{{ $average_per_hour }}/sph</div>
                <div class="ecl-fact-figures__title">Statements per hour</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="infographic"/>
                </svg>
                <div class="ecl-fact-figures__value">{{ $platforms_total }} platforms</div>
                <div class="ecl-fact-figures__title">Active platforms</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="growth"/>
                </svg>
                <div class="ecl-fact-figures__value">{{ $average_per_hour_per_platform }}/sphpp</div>
                <div class="ecl-fact-figures__title">Statements per hour per platform</div>
            </div>

        </div>
    </div>

    <div class="ecl-row">
        <div class="ecl-col-6">
            <h3 class="ecl-u-type-heading-3">Most Active Platforms</h3>
            <ul class="ecl-unordered-list">
                @foreach($top_platforms as $top_platform)
                    <li class="ecl-unordered-list__item">
                        <a href="{{ route('analytics.platform', [$top_platform->uuid]) }}" class="ecl-link--standalone">{{ $top_platform->name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="ecl-col-6">
            <h3 class="ecl-u-type-heading-3">Most Used Categories</h3>
            <ul class="ecl-unordered-list">
                @foreach($top_categories as $top_category)
                    <li class="ecl-unordered-list__item">
                        {{ Statement::STATEMENT_CATEGORIES[$top_category->value] }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <h2 class="ecl-u-type-heading-2">Statements over {{ $last_history_days }} days</h2>

    <x-analytics.line-chart :values="array_reverse($day_totals_values)" :labels="array_reverse($day_totals_labels)" height="400"/>

@endsection
