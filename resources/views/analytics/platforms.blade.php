@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Platforms"/>
@endsection


@section('content')

    <x-analytics.header />

    <div class="ecl-u-d-flex ecl-u-justify-content-between">

        <div>
            <h2 class="ecl-u-type-heading-2">Platform Statements for the Last {{ $last_days }} Days</h2>
        </div>

        <div>
            <p class="ecl-u-type-paragraph">
                <a class="ecl-link" href="{{ route('analytics.platform') }}">View Analytics for Individual Platforms</a>
            </p>
        </div>

    </div>

    <x-analytics.bar-chart :values="$platform_totals_values" :labels="$platform_totals_labels" height="800"/>

@endsection
