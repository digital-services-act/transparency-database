@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Grounds"/>
@endsection


@section('content')

    <x-analytics.header />

    <h2 class="ecl-u-type-heading-2">Grounds for the Last @aif($last_days) Days</h2>

    <x-analytics.bar-chart :values="$ground_totals_values" :labels="$ground_totals_labels" height="200"/>

@endsection
