@extends('layouts/ecl')

@section('title', 'Manage Platforms')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="Dashboard" url="{{ route('dashboard') }}" />
    <x-ecl.breadcrumb label="Platforms" />
@endsection


@section('content')

    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">
        <form method="get">
            <x-ecl.textfield name="s" label="Search <a class='ecl-link' href='{{ route('platform.index') }}'>reset</a>" placeholder="search by name" justlabel="true" value="{{ request()->get('s', '') }}" />
        </form>
    </div>

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Platforms</h1>

    <p class="ecl-u-type-paragraph">
        Manage the platforms of the application below.
    </p>

    <p class="ecl-u-type-paragraph">
        <x-ecl.cta-button label="Create a Platform" url="{{ route('platform.create') }}"/>
    </p>

    <table class="ecl-table ecl-table--zebra">
        <thead class="ecl-table__head">
        <tr class="ecl-table__row">
            <th class="ecl-table__header">Name</th>

            <th class="ecl-table__header" width="25%">Actions</th>
        </tr>
        </thead>
        <tbody class="ecl-table__body">
        @foreach($platforms as $platform)
            <tr class="ecl-table__row">
                <td class="ecl-table__cell">

                    <x-ecl.external-link href="{{ $platform->url }}" label="{{ $platform->name }}"/>

                </td>



                <td class="ecl-table__cell">
                    <button class="ecl-u-d-inline ecl-u-f-l ecl-u-mr-m ecl-button ecl-button--secondary" onclick="document.location.href = '{{ route('platform.edit', [$platform]) }}'">edit</button>

                    <form action="{{ route('platform.destroy', [$platform]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="submit" class="ecl-u-d-inline ecl-u-f-l ecl-button ecl-button--secondary" value="delete" />
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>


    {{ $platforms->links('paginator') }}


@endsection
