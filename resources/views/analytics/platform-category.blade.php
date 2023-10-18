@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Platform and Category"/>
@endsection


@section('content')

    <x-analytics.header/>

    <div class="ecl-row ecl-u-mb-l">
        <div class="ecl-col-l-6">
            <h2 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">
                @if($platform)
                    <a class="ecl-link ecl-link--standalone" href="{{ route('analytics.platform', [$platform->uuid]) }}">{{ $platform->name }}</a>
                @else
                    Platform
                @endif
                &
                @if($category)
                        <a class="ecl-link ecl-link--standalone" href="{{ route('analytics.category', [$category]) }}">{{ Statement::STATEMENT_CATEGORIES[$category] }}</a>
                @else
                    Category
                @endif
            </h2>
        </div>
        <div class="ecl-col-l-6">
            <form method="get" id="platform">
                <x-ecl.select label="Select a Platform" name="uuid" id="uuid"
                              justlabel="true"
                              :options="$options['platforms']" :default="request()->get('uuid')"
                />
                <x-ecl.select label="Select a Category" name="category" id="category"
                              justlabel="true"
                              :options="$options['categories']" :default="request()->get('category')"
                />
                <x-ecl.button label="GO"/>
            </form>
        </div>
    </div>

    @if($platform_category_report)
        <x-analytics.category-report :category_report="$platform_category_report" :days_ago="$days_ago" :months_ago="$months_ago"/>
    @endif

@endsection
