@php use Symfony\Component\VarDumper\VarDumper; @endphp
@extends('layouts/ecl')

@section('title', 'Manage Log Messages')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Log Messages" />
@endsection


@section('content')



    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Log Messages</h1>




    <table class="ecl-table ecl-table--zebra">
        <thead class="ecl-table__head">
        <tr class="ecl-table__row">
            <th class="ecl-table__header">Id</th>
            <th class="ecl-table__header">Date</th>
            <th class="ecl-table__header">Message</th>
            <th class="ecl-table__header">Context</th>

        </tr>
        </thead>
        <tbody class="ecl-table__body">
        @foreach($log_messages as $log_message)
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Id">{{ $log_message->id }}</td>
                <td class="ecl-table__cell" data-ecl-table-header="Date">{{ $log_message->logged_at }}</td>
                <td class="ecl-table__cell" data-ecl-table-header="Message">
                    <x-ecl.expandable :label="substr($log_message->message, 0, 20)">{{ $log_message->message }}</x-ecl.expandable>
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Context">
                    {{ VarDumper::dump($log_message->context->collect()) }}
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $log_messages->links('paginator') }}

@endsection
