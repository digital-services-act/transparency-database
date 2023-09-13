@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Restrictions"/>
@endsection


@section('content')

    <x-analytics.header />

    <h2 class="ecl-u-type-heading-2">Restrictions for the Last {{ $last_days }} Days</h2>

    <x-analytics.bar-chart :values="$restriction_totals_values" :labels="$restriction_totals_labels" height="500"/>

@endsection
