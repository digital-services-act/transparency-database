@extends('layouts/ecl')

@section('title', 'Not Found')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Not Found"/>
@endsection

@section('extra-head')
    <style>

        code {
            background: #2b2b2b;
            color: #f8f8f2;
            padding: .1em;
        }

        hr {
            border: none;
            border-top: 2px solid;
            color: rgb(205, 213, 239);
        }

    </style>
@endsection

@section('content')

    <h1 class="ecl-u-type-heading-1">404 - Not Found</h1>




    <div class="ecl-row">
        <div class="ecl-col-l-12" id="content-wrapper">
            <div id="content-area">
                <p class="ecl-u-type-paragraph">
                    Sincere apologies, the resource that you are looking for is not found.
                </p>
            </div>
        </div>
    </div>



    <div class="ecl-row">
        <div class="ecl-col-l-12" id="content-wrapper">
            <div id="content-area">
                <p class="ecl-u-type-paragraph">
                    <x-ecl.cta-button label="Return to the homepage" url="/" />
                </p>
            </div>
        </div>
    </div>

    <div class="ecl-row ecl-u-mb-xl-4xl">&nbsp;</div>
    <div class="ecl-row ecl-u-mb-xl-4xl">&nbsp;</div>
    <div class="ecl-row ecl-u-mb-xl-4xl">&nbsp;</div>

@endsection
