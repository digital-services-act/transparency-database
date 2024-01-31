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
        <iframe src="https://app.powerbi.com/view?r=eyJrIjoiN2VkNzI3OGEtMTM5My00NjEyLTljODMtYTVlMWI4MDYzNDhmIiwidCI6ImIyNGM4YjA2LTUyMmMtNDZmZS05MDgwLTcwOTI2ZjhkZGRiMSIsImMiOjh9" allowfullscreen></iframe>
    </div>

@endsection

