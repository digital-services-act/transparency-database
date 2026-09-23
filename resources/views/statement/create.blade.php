@extends('layouts/ecl')

@section('title', 'Create a statement of reasons')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Create a statement of reasons" />
@endsection

@section('content')

{{--    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Create a statement of reason</h1>--}}

    <h1 class="ecl-u-type-heading-1">Submit a statement of reasons</h1>

    <p class="ecl-u-type-paragraph">
        The webform is made available as a submission method for the providers of online platforms that expect to submit a low volume of statements of reasons to the DSA transparency database.<br/>
        For more information on the webform submission, please have a look at the <a href="/page/webform-documentation">webform documentation</a>
    </p>

    <form method="post" action="{{route('statement.store')}}" id="create-statement-form">
        @csrf
        <x-statement.form :statement="$statement" :options="$options" />
    </form>

    <button class="ecl-button ecl-button--primary" onClick="document.getElementById('create-statement-form').submit();">Create the statement of reasons</button>
    <script>

    </script>

@endsection
