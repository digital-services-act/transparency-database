@extends('layouts/ecl')

@section('title', 'Statements')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Search for Statements of Reasons" url="{{ route('statement.index') }}"/>
    <x-ecl.breadcrumb label="Advanced Search"/>
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Statements of Reasons: Advanced Search</h1>


    <p class="ecl-u-type-paragraph">
        With the form below you can now specify many more filters on the statements of reasons.
        To submit feedback on the content of this page and to propose additional features, please visit the
        <a href="{{ route('feedback.index') }}" class="ecl-link ecl-link--standalone">link</a> to the feedback form.
    </p>
    <p class="ecl-u-type-paragraph">
        {!! __('dayarchive.Please note that a Data Retention Policy applies and the daily dumps will be available during a limited period following their creation date.') !!}</p>


    <x-statement.search-form :options="$options"/>

@endsection

