@extends('layouts/ecl')

@section('title', 'Home')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" />
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1">Home</h1>

    <p class="ecl-u-type-paragraph">
        The DSA Transparency database collects and analyzes statements of reasons,
        helping Internet users to know their rights and understand the law. These
        data enable us to study the prevalence of legal threats and let Internet
        users see the source of content removals.
    </p>

@endsection
