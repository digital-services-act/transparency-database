@php use App\Models\Statement; @endphp
@extends('layouts/ecl')

@section('title', 'Analytics')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Analytics" url="{{ route('analytics.index') }}"/>
    <x-ecl.breadcrumb label="Dashboard" />
@endsection


@section('content')

    <iframe title="Analytics Dashboard" width="1300" height="1000" src="https://app.powerbi.com/view?r=eyJrIjoiNDVkZjM2MDAtOTYyOS00YzUxLTlhMzgtNjI4ODlkMjA0NmJiIiwidCI6ImIyNGM4YjA2LTUyMmMtNDZmZS05MDgwLTcwOTI2ZjhkZGRiMSIsImMiOjh9" frameborder="0" allowFullScreen="true"></iframe>

@endsection
