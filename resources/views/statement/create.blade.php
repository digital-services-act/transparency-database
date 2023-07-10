@extends('layouts/ecl')

@section('title', 'Create a statement')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Statements" url="{{ route('statement.index') }}" />
    <x-ecl.breadcrumb label="Create a Statement" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create a Statement</h1>

    <form method="post" action="{{route('statement.store')}}" id="create-statement-form">
        @csrf
        <x-statement-form :statement="$statement" :options="$options" />
{{--        <x-ecl.button label="Create statement" type="submit" id="submitter"/>--}}
    </form>

    <button class="ecl-button ecl-button--primary" onClick="document.getElementById('create-statement-form').submit();">Create Statement</button>
    <script>

    </script>

@endsection

