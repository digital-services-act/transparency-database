@extends('layouts/ecl')

@section('title', 'Auditing Logs')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Auditing Logs" />
@endsection


@section('content')



    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Auditing Logs</h1>

    <p>
        View and search the audit logs below.
    </p>


    <table class="ecl-table ecl-table--zebra">
        <thead class="ecl-table__head">
            <tr class="ecl-table__row">
                <th class="ecl-table__header">Event</th>
                <th class="ecl-table__header">Model</th>
                <th class="ecl-table__header">Model Id</th>
                <th class="ecl-table__header">Causer</th>
                <th class="ecl-table__header">Causer Id</th>
                <th class="ecl-table__header">Date</th>
            </tr>
        </thead>
        <tbody class="ecl-table__body">
            @foreach($logs as $log)
                <tr class="ecl-table__row">
                    <td class="ecl-table__cell">{{ $log->event }}</td>
                    <td class="ecl-table__cell">{{ str_replace("App\\Models\\", "", $log->subject_type) }}</td>
                    <td class="ecl-table__cell">
                        {{ $log->subject_id }}
                        @if($log->subject_type == 'App\Models\Statement')
                            <a class="ecl-link" href="{{ route('statement.show', ['statement' => \App\Models\Statement::find($log->subject_id)?->uuid]) }}">
                                view
                            </a>
                        @endif
                    </td>
                    <td class="ecl-table__cell">{{ str_replace("App\\Models\\", "", $log->causer_type ) }}</td>
                    <td class="ecl-table__cell">
                        {{ $log->causer_id }}
                        @if($log->causer_type == 'App\Models\User')
                            (<a href="mailto:{{$log->causer->email}}">{{ $log->causer->name }}</a>)
                        @endif
                    </td>
                    <td class="ecl-table__cell">{{ $log->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    {{ $logs->links('paginator') }}


@endsection
