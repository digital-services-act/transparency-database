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

    <h1 class="ecl-page-header__title">Notices</h1>

    <table class="ecl-table ecl-table--zebra">
        <thead class="ecl-table__head">
        <tr class="ecl-table__row">
            <th class="ecl-table__header">Title</th>
            <th class="ecl-table__header">Entities</th>
            <th class="ecl-table__header"></th>
            <th class="ecl-table__header"></th>
            <th class="ecl-table__header"></th>
        </tr>
        </thead>
        <tbody class="ecl-table__body">
        @foreach($notices as $notice)
            <tr class="ecl-table__row">
                <td class="ecl-table__cell"><a class="ecl-link" href="{{ route('notice.show', [$notice]) }}">{{ $notice->title }}</a></td>
                <td class="ecl-table__cell">{{ implode(', ', $notice->entities()->pluck('name')->toArray()) }}</td>
                <td class="ecl-table__cell"></td>
                <td class="ecl-table__cell"></td>
                <td class="ecl-table__cell"></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $notices->links('paginator') }}

@endsection

