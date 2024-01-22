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

    <p class="ecl-u-type-paragraph">
        The dashboard below provides a user-friendly and interactive interface to explore summarized data, offering a comprehensive overview. Click on different elements to navigate. For detailed guidance on optimizing your use of this tool, please refer to the 'Instructions' panel.
    </p>

    <div class="responsive-iframe-container">
        <iframe src="https://app.powerbi.com/reportEmbed?reportId=55eee05f-64ba-4f38-9437-5f8c7a004a13&autoAuth=true&ctid=b24c8b06-522c-46fe-9080-70926f8dddb1" allowfullscreen></iframe>
    </div>

@endsection

