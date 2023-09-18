@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Platforms"/>
@endsection


@section('content')

    <x-analytics.header />

    <div class="ecl-u-d-flex ecl-u-justify-content-between ecl-u-mb-l">
        <div>
            <h2 class="ecl-u-type-heading-2">Platform Statements of Reasons for the Last {{ $last_days }} Days</h2>
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

    <x-analytics.bar-chart :values="$platform_totals_values" :labels="$platform_totals_labels" height="800"/>

@endsection
