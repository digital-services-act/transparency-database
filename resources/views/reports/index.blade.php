@extends('layouts/ecl')

@section('title', 'Reports')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Reports" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Reports</h1>

    @can('administrate')
        <p class="ecl-u-type-paragraph">
            <a class="ecl-link" href="{{ route('reports.for.platform') }}">View Reports for Individual Platforms</a>
        </p>
    @endcan

    <x-platform.report :platform="$platform" :platform_report="$platform_report" :days_ago="$days_ago" :months_ago="$months_ago" />

@endsection
