@extends('layouts/ecl')

@section('title', 'Feedback Form')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Feedback Form" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Feedback Form</h1>

    <form method="post" action="{{route('feedback.send')}}" id="send-feedback-form">
        @honeypot
        @csrf
        <x-ecl.textarea label="Your feedback" name="feedback" id="feedback"
                        required="true" rows="10"/>
        <button class="ecl-button ecl-button--primary" onClick="document.getElementById('send-feedback-form').submit();">Send the Feedback</button>
    </form>










@endsection
