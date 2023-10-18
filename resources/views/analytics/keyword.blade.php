@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Keywords" url="{{ route('analytics.keywords') }}"/>
    <x-ecl.breadcrumb label="Keyword"/>
@endsection


@section('content')

    <x-analytics.header/>

    <div class="ecl-row">
        <div class="ecl-col-l-6">
            <h2 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">@if($keyword)
                    {{ Statement::KEYWORDS[$keyword] }}
                @else
                    Keyword
                @endif</h2>
        </div>
        <div class="ecl-col-l-6">
            <form method="get" id="keyword">
                <x-ecl.select label="Select a Keyword" name="keyword" id="keyword"
                              justlabel="true"
                              :options="$options['keywords']" :default="request()->route('keyword')"
                />
            </form>
            <script>
              var keyword = document.getElementById('keyword');
              keyword.onchange = (event) => {
                document.location.href = '{{ route('analytics.keyword') }}/' + event.target.value;
              }
            </script>
        </div>
    </div>

    @if($keyword_report)
        <x-analytics.keyword-report :keyword_report="$keyword_report" :days_ago="$days_ago" :months_ago="$months_ago"/>
    @endif


@endsection
