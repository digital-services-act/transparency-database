@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')

    <style>
        .responsive-iframe-container {
            position: relative;
            overflow: hidden;
            width: 110%;
            padding-top: 63%;
            margin-left:-20px;
        }

        .responsive-iframe-container iframe {
            position: absolute;

            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>

    <h1 class="ecl-u-type-heading-1">Dashboard</h1>

    <div class="ecl-u-type-paragraph">
        The dashboard below provides a user-friendly and interactive interface to explore summarized data, offering a comprehensive overview. Start exploring the data by clicking on different elements. You can navigate across the following pages, from left to right at the bottom of the dashboard: 1) Overview, 2) Timelines, 3) Violations, 4) Restrictions, 5) Other analysis.
        For additional guidance on making the best use of the tool, please refer to page 6 - Instructions.
    </div>

    <div class="responsive-iframe-container">
        <iframe title="Transparency Database Dashboard - {{config('app.env_real')}}" width="800" height="636" src="{{config("dsa.POWERBI")}}"
                frameborder="0" allowFullScreen="true"></iframe>
    </div>



@endsection

