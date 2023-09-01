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

    <x-analytics.bar-chart :data="$category_totals" height="800"/>

@endsection