@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Restrictions"/>
@endsection


@section('content')

    <x-analytics.header />

{{--    <div class="ecl-u-d-flex ecl-u-justify-content-between ecl-u-mb-l">--}}
{{--        <div>--}}
{{--            <h2 class="ecl-u-type-heading-2">Restrictions for the Last {{ $last_days }} Days</h2>--}}
{{--        </div>--}}
{{--        <div>--}}
{{--            <form method="get" id="category">--}}
{{--                <x-ecl.select label="Select a Keyword" name="keyword" id="keyword"--}}
{{--                              justlabel="true"--}}
{{--                              :options="$options['keywords']" :default="request()->route('keyword')"--}}
{{--                />--}}
{{--            </form>--}}
{{--            <script>--}}
{{--              var category = document.getElementById('keyword');--}}
{{--              category.onchange = (event) => {--}}
{{--                document.location.href = '{{ route('analytics.keyword') }}/' + event.target.value;--}}
{{--              }--}}
{{--            </script>--}}
{{--        </div>--}}
{{--    </div>--}}

<h1>Visibility Restrictions for the Last {{ $last_days }} Days</h1>
    <x-analytics.bar-chart :values="$restrictions_data['decision_visibility']['values']" :labels="$restrictions_data['decision_visibility']['labels']" height="800" id="decision_visibility_chart"/>
<hr>
    <h1>Monetary Restrictions for the Last {{ $last_days }} Days</h1>
    <x-analytics.bar-chart :values="$restrictions_data['decision_monetary']['values']" :labels="$restrictions_data['decision_monetary']['labels']" height="800" id="decision_monetary_chart"/>
<hr>
    <h1>Provision Restrictions for the Last {{ $last_days }} Days</h1>
    <x-analytics.bar-chart :values="$restrictions_data['decision_provision']['values']" :labels="$restrictions_data['decision_provision']['labels']" height="800" id="decision_provision_chart"/>

    <hr>
    <h1>Account Restrictions for the Last {{ $last_days }} Days</h1>
    <x-analytics.bar-chart :values="$restrictions_data['decision_account']['values']" :labels="$restrictions_data['decision_account']['labels']" height="800" id="decision_account_chart"/>

@endsection
