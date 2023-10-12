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

    <div class="ecl-u-d-flex ecl-u-justify-content-between ecl-u-mb-l">
        <div>
            <h2 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">
                @if($platform)
                    {{ $platform->name }}
                @else
                    Platform
                @endif
                &
                @if($category)
                    {{ Statement::STATEMENT_CATEGORIES[$category] }}
                @else
                    Category
                @endif
            </h2>
        </div>
        <div>
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
            <script>
              // var uuid = document.getElementById('uuid');
              // var category = document.getElementById('category');

              // re-use the paltform route so we get all the https:// stuff and then build manually.
              {{--uuid.onchange = (event) => {--}}
              {{--  document.location.href = '{{ route('analytics.platform') }}/' + uuid.value + '/category/' +--}}
              {{--    category.value;--}}
              {{--}--}}

              {{--category.onchange = (event) => {--}}
              {{--  document.location.href = '{{ route('analytics.platform') }}/' + uuid.value + '/category/' +--}}
              {{--    category.value;--}}
              {{--}--}}
            </script>
        </div>
    </div>

    @if($platform_category_report)
        <x-analytics.category-report :category_report="$platform_category_report" :days_ago="$days_ago" :months_ago="$months_ago"/>
    @endif

@endsection
