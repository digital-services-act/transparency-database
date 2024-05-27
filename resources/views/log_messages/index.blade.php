@php use Symfony\Component\VarDumper\VarDumper; @endphp
@extends('layouts/ecl')

@section('title', 'Log Messages')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="Log Messages" />
@endsection


@section('content')


    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">
        <form method="get">
            <x-ecl.textfield name="s" label="Search <a class='ecl-link' href='{{ route('log-messages.index') }}'>reset</a>" placeholder="freetext search" justlabel="true" value="{{ request()->get('s', '') }}" />
        </form>
    </div>

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Log Messages</h1>


    <form action="{{ route('log-messages.destroy') }}" method="POST">
        @csrf
        @method('DELETE')
        <input type="submit" class="ecl-u-d-inline ecl-u-f-l ecl-button ecl-button--secondary" value="truncate" />
    </form>

    <table class="ecl-table ecl-table--zebra">
        <thead class="ecl-table__head">
        <tr class="ecl-table__row">
            <th class="ecl-table__header">Id</th>
            <th class="ecl-table__header">Date</th>
            <th class="ecl-table__header">Level</th>
            <th class="ecl-table__header">Message</th>
            <th class="ecl-table__header">Context</th>
        </tr>
        </thead>
        <tbody class="ecl-table__body">
        @foreach($log_messages as $log_message)
            <tr class="ecl-table__row">
                <td class="ecl-table__cell" data-ecl-table-header="Id">{{ $log_message->id }}</td>
                <td class="ecl-table__cell" data-ecl-table-header="Date">{{ $log_message->logged_at }}</td>
                <td class="ecl-table__cell" data-ecl-table-header="Date">{{ $log_message->level_name }}</td>
                <td class="ecl-table__cell" data-ecl-table-header="Message">
                    {{ substr($log_message->message, 0, 100) }}
                </td>
                <td class="ecl-table__cell" data-ecl-table-header="Context">
                    {{ VarDumper::dump($log_message->context->collect()->toArray()) }}
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $log_messages->links('paginator') }}

@endsection
