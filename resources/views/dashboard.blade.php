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
            width: 100%;
            padding-top: 56.25%; /* 16:9 aspect ratio */
        }

        .responsive-iframe-container iframe {
            position: absolute;
            margin-left:-20px;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>

    <h1 class="ecl-u-type-heading-1">Dashboard</h1>

    <p class="ecl-u-type-paragraph">
        The dashboard below allows you to explore summarised data in an easy and user-friendly manner, for a comprehensive overview. For more information on how to use it most effectively, please see its 'Instructions' panel.
    </p>

    <div class="responsive-iframe-container">
        <iframe src="https://app.powerbi.com/reportEmbed?reportId=55eee05f-64ba-4f38-9437-5f8c7a004a13&autoAuth=true&ctid=b24c8b06-522c-46fe-9080-70926f8dddb1" allowfullscreen></iframe>
    </div>

@endsection

