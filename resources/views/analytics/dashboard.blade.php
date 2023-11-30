@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')

    <iframe title="Transparency Database Dashboard (v.1.0)" width="1300" height="760" src="https://app.powerbi.com/reportEmbed?reportId=e5b3a8bb-2c85-43b8-82da-957cb07e7af6&autoAuth=true&ctid=b24c8b06-522c-46fe-9080-70926f8dddb1" frameborder="0" allowFullScreen="true"></iframe>

@endsection
