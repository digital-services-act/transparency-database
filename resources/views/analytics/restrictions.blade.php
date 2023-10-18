@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Restrictions"/>
@endsection


@section('content')

    <x-analytics.header />

    <h2 class="ecl-u-type-heading-2">Visibility Restrictions for the Last @aif($last_days) Days</h2>

    <x-analytics.bar-chart :values="$restrictions_data['decision_visibility']['values']" :labels="$restrictions_data['decision_visibility']['labels']" height="400" id="decision_visibility_chart"/>

    <h2 class="ecl-u-type-heading-2">Monetary Restrictions for the Last @aif($last_days) Days</h2>

    <x-analytics.bar-chart :values="$restrictions_data['decision_monetary']['values']" :labels="$restrictions_data['decision_monetary']['labels']" height="200" id="decision_monetary_chart"/>


    <h2 class="ecl-u-type-heading-2">Provision Restrictions for the Last @aif($last_days) Days</h2>

    <x-analytics.bar-chart :values="$restrictions_data['decision_provision']['values']" :labels="$restrictions_data['decision_provision']['labels']" height="250" id="decision_provision_chart"/>


    <h2 class="ecl-u-type-heading-2">Account Restrictions for the Last @aif($last_days) Days</h2>

    <x-analytics.bar-chart :values="$restrictions_data['decision_account']['values']" :labels="$restrictions_data['decision_account']['labels']" height="150" id="decision_account_chart"/>

@endsection
