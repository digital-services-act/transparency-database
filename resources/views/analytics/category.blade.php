@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Categories" url="{{ route('analytics.categories') }}"/>
    <x-ecl.breadcrumb label="Category"/>
@endsection


@section('content')

    <x-analytics.header/>

    <div class="ecl-row">
        <div class="ecl-col-l-6">
            <h2 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">@if($category)
                    {{ Statement::STATEMENT_CATEGORIES[$category] }}
                @else
                    Category
                @endif</h2>
        </div>
        <div class="ecl-col-l-6">
            <form method="get" id="category">
                <x-ecl.select label="Select a Category" name="category" id="category"
                              justlabel="true"
                              :options="$options['categories']" :default="request()->route('category')"
                />
            </form>
            <script>
              var category = document.getElementById('category');
              category.onchange = (event) => {
                document.location.href = '{{ route('analytics.category') }}/' + event.target.value;
              }
            </script>
        </div>
    </div>

    @if($category_report)
        <x-analytics.category-report :category="$category" :category_report="$category_report" :days_ago="$days_ago" :months_ago="$months_ago"/>
    @endif

@endsection
