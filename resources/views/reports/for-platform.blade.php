@extends('layouts/ecl')

@section('title', 'Reports')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}"/>
    <x-ecl.breadcrumb label="Reports" url="{{ route('reports') }}"/>
    <x-ecl.breadcrumb label="for Platform"/>
@endsection


@section('content')

    <div class="ecl-u-d-flex ecl-u-justify-content-between">

        <div>
            <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Reports for Platform</h1>
        </div>

        <div>
            <form method="get">
                <x-ecl.select label="Select a Platform" name="platform_id" id="platform_id"
                              justlabel="true"
                              :options="$options['platforms']" :default="request()->get('platform_id', [])"
                />

                <x-ecl.button label="View"/>
            </form>
        </div>

    </div>

    @if($platform)

        <x-platform.report :platform="$platform" :platform_report="$platform_report" :days_ago="$days_ago" :months_ago="$months_ago" />

    @endif

@endsection
