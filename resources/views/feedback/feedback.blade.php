@extends('layouts/ecl')

@section('title', 'Feedback and support')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
    <x-ecl.breadcrumb label="Feedback and support<" />
@endsection


@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Feedback and support</h1>


    <p class="ecl-u-type-paragraph ecl-u-mb-l">
        For feedback, questions, or assistance related to the DSA Transparency Database,
        please email the <a href="mailto:CNECT-DSA-HELPDESK@ec.europa.eu">DSA Helpdesk</a>.
        <br />
        <br />
        Please include a clear description of your enquiry and avoid sharing sensitive personal information.
    </p>










@endsection
