@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Categories"/>
@endsection


@section('content')

    <x-analytics.header />

    <div class="ecl-row">
        <div class="ecl-col-l-6">
            <h2 class="ecl-u-type-heading-2">Categories for the Last @aif($last_days) Days</h2>
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


    <x-analytics.bar-chart :values="$category_totals_values" :labels="$category_totals_labels" height="800"/>

@endsection
