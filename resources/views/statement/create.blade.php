@extends('layouts/ecl')

@section('title', 'Create a statement of Reasons')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Statements of Reasons" url="{{ route('statement.index') }}" />
    <x-ecl.breadcrumb label="Create a Statement of Reason" />
@endsection

@section('content')

{{--    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create a Statement of Reason</h1>--}}

    <h1 class="ecl-u-type-heading-1">Submit a Statement of Reason</h1>

    <p class="ecl-u-type-paragraph">
        For more information on the fields, please have a look at the <a href="{{route('profile.page.show', ['documentation'])}}">global documentation</a>
    </p>

    <form method="post" action="{{route('statement.store')}}" id="create-statement-form">
        @csrf
        <x-statement.form :statement="$statement" :options="$options" />
    </form>

    <button class="ecl-button ecl-button--primary" onClick="document.getElementById('create-statement-form').submit();">Create the Statement of Reason</button>
    <script>

    </script>

@endsection

