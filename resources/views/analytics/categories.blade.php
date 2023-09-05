@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Categories"/>
@endsection


@section('content')

    <x-analytics.header />

    <h2 class="ecl-u-type-heading-2">Categories for the Last {{ $last_days }} Days</h2>

    <x-analytics.bar-chart :values="$category_totals_values" :labels="$category_totals_labels" height="800"/>

@endsection
