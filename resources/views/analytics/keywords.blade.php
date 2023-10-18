@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Categories Specifications"/>
@endsection


@section('content')

    <x-analytics.header />

    <div class="ecl-row">
        <div class="ecl-col-l-6">
            <h2 class="ecl-u-type-heading-2">Keywords for the Last @aif($last_days) Days</h2>
        </div>
        <div class="ecl-col-l-6">
            <form method="get" id="category">
                <x-ecl.select label="Select a Keyword" name="keyword" id="keyword"
                              justlabel="true"
                              :options="$options['keywords']" :default="request()->route('keyword')"
                />
            </form>
            <script>
              var category = document.getElementById('keyword');
              category.onchange = (event) => {
                document.location.href = '{{ route('analytics.keyword') }}/' + event.target.value;
              }
            </script>
        </div>
    </div>


    <x-analytics.bar-chart :values="$keyword_totals_values" :labels="$keyword_totals_labels" height="1200"/>

@endsection
