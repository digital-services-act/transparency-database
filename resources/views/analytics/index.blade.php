@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics"/>
@endsection


@section('content')

    <x-analytics.header />

    <h2 class="ecl-u-type-heading-2">Overview</h2>

    <div class="ecl-fact-figures ecl-fact-figures--col-3">
        <div class="ecl-fact-figures__items">

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="infographic"/>
                </svg>
                <div class="ecl-fact-figures__value">{{ $total }}</div>
                <div class="ecl-fact-figures__title">Statements All Time Total</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="growth"/>
                </svg>
                <div class="ecl-fact-figures__value">{{ $total_last_days }}</div>
                <div class="ecl-fact-figures__title">Statements last {{ $last_days }} Days</div>
            </div>

            <div class="ecl-fact-figures__item">
                <svg class="ecl-icon ecl-icon--m ecl-fact-figures__icon" focusable="false" aria-hidden="true">
                    <x-ecl.icon icon="growth"/>
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
                    <x-ecl.icon icon="growth"/>
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

@endsection
