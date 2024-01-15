@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')

    <h1 class="ecl-u-type-heading-1">Dashboard</h1>

    <p class="ecl-u-type-paragraph">
        The dashboard below allows you to explore summarised data in an easy and user-friendly manner, for a comprehensive overview. For more information on how to use it most effectively, please see its 'Instructions' panel.
    </p>

<div style="width:100%; margin-left:-40px">
    <iframe title="Transparency Database Dashboard (v.1.4)" width="1400" height="780" src="https://app.powerbi.com/reportEmbed?reportId=55eee05f-64ba-4f38-9437-5f8c7a004a13&autoAuth=true&ctid=b24c8b06-522c-46fe-9080-70926f8dddb1"  allowFullScreen="true"></iframe>
</div>
@endsection
