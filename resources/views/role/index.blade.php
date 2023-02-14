@extends('layouts/ecl')

@section('title', 'Manage Roles')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Roles" />
@endsection


@section('content')

    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">
        <x-ecl.cta-button label="Create a Role" url="{{ route('role.create') }}"/>
    </div>

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Roles</h1>

    <p>
        Manage the roles of the application below.
    </p>


    <table class="ecl-table ecl-table--zebra">
        <thead class="ecl-table__head">
        <tr class="ecl-table__row">
            <th class="ecl-table__header">Role</th>
            <th class="ecl-table__header"></th>
            <th class="ecl-table__header"></th>
            <th class="ecl-table__header"></th>
            <th class="ecl-table__header">Actions</th>
        </tr>
        </thead>
        <tbody class="ecl-table__body">
        @foreach($roles as $role)
            <tr class="ecl-table__row">
                <td class="ecl-table__cell">{{ $role->name }}</td>
                <td class="ecl-table__cell"></td>
                <td class="ecl-table__cell"></td>
                <td class="ecl-table__cell"></td>
                <td class="ecl-table__cell">
                    <button class="ecl-u-d-inline ecl-u-f-l ecl-u-mr-m ecl-button ecl-button--secondary" onclick="document.location.href = '{{ route('role.edit', [$role]) }}'">edit</button>

                    <form action="{{ route('role.destroy', [$role]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="submit" class="ecl-u-d-inline ecl-u-f-l ecl-button ecl-button--secondary" value="delete" />
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>


@endsection
