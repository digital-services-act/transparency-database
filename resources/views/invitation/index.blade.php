@extends('layouts/ecl')

@section('title', 'Manage Invitations')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="Invitations"/>
@endsection


@section('content')

    {{--    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">--}}
    {{--        <form method="get">--}}
    {{--            <x-ecl.textfield name="s" label="Search <a class='ecl-link' href='{{ route('user.index') }}'>reset</a>" placeholder="search by email/name/platform" justlabel="true" value="{{ request()->get('s', '') }}" />--}}
    {{--        </form>--}}
    {{--    </div>--}}

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Invitations</h1>

    <p class="ecl-u-type-paragraph">
        Manage the invitations of the application below.
    </p>

    <p class="ecl-u-type-paragraph">
        <x-ecl.cta-button label="Create an Invitation" url="{{ route('invitation.create') }}"/>
    </p>

    <table class="ecl-table ecl-table--zebra">
        <thead class="ecl-table__head">
        <tr class="ecl-table__row">
            <th class="ecl-table__header">Email</th>
            <th class="ecl-table__header">Platform</th>
            <th class="ecl-table__header" width="25%">Actions</th>
        </tr>
        </thead>
        <tbody class="ecl-table__body">
        @foreach($invitations as $invitation)
            <tr class="ecl-table__row">
                <td class="ecl-table__cell">{{ $invitation->email }}</td>
                <td class="ecl-table__cell">{{ $invitation->platform->name ?? '' }}</td>
                <td class="ecl-table__cell">
                    <button class="ecl-u-d-inline ecl-u-f-l ecl-u-mr-m ecl-button ecl-button--secondary"
                            onclick="document.location.href = '{{ route('invitation.edit', [$invitation]) }}'">edit
                    </button>

                    <form action="{{ route('invitation.destroy', [$invitation]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="submit" class="ecl-u-d-inline ecl-u-f-l ecl-button ecl-button--secondary"
                               value="delete"/>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>


    {{ $invitations->links('paginator') }}

@endsection
