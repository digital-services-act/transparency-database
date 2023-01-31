@extends('layouts/ecl')

@section('title', 'Notices')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Notices"/>
@endsection

@section('content')



    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">
        <x-ecl.cta-button label="Create a Notice" url="{{ route('notice.create') }}"/>
    </div>

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Notices</h1>

    <x-notices-table :notices=$notices />

@endsection

