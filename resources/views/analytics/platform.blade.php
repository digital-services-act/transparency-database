@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Platforms" url="{{ route('analytics.platforms') }}"/>
    <x-ecl.breadcrumb label="Platform"/>
@endsection


@section('content')

    <x-analytics.header />

    <div class="ecl-u-d-flex ecl-u-justify-content-between ecl-u-mb-l">
        <div>
            <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Analytics for @if($platform){{ $platform->name }} @else Platform @endif</h1>
        </div>
        <div>
            <form method="get" id="platform">
                <x-ecl.select label="Select a Platform" name="uuid" id="uuid"
                              justlabel="true"
                              :options="$options['platforms']" :default="request()->route('uuid')"
                />
            </form>
            <script>
              var uuid = document.getElementById('uuid');
              uuid.onchange = (event) => {
                document.location.href = '{{ route('analytics.platform') }}/' + event.target.value;
              }
            </script>
        </div>
    </div>

    @if($platform && $platform_report)
        <x-platform.report :platform="$platform" :platform_report="$platform_report" :days_ago="$days_ago" :months_ago="$months_ago" />
    @endif

@endsection
