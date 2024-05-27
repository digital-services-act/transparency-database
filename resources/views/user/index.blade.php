@extends('layouts/ecl')

@section('title', 'Manage Users')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="{{__('menu.Home')}}" url="{{ route('home') }}"/>
    <x-ecl.breadcrumb label="User Profile" url="{{ route('profile.start') }}" />
    <x-ecl.breadcrumb label="Users" />
@endsection


@section('content')

    <div class="ecl-u-mt-l ecl-u-mb-l ecl-u-f-r">
        <form method="get" id="searchform">
            <x-ecl.textfield name="s" label="Search <a class='ecl-link' href='{{ route('user.index') }}'>reset</a>" placeholder="search by email/name/platform" justlabel="true" value="{{ request()->get('s', '') }}" />
            <x-ecl.select :options="$platforms" label="Platform" :justlabel="true" name="uuid" id="platform_select" :default="request()->get('uuid')" :allow_null="true"/>
        </form>
        <script>
            let sel = document.getElementById('platform_select');
            let searchform = document.getElementById('searchform');
            sel.addEventListener('change', (e) => {
              searchform.submit();
            });
        </script>
    </div>

    <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Users</h1>

    <p class="ecl-u-type-paragraph">
        Manage the users of the application below.
    </p>

    <p class="ecl-u-type-paragraph">
        <x-ecl.cta-button label="Create a User" url="{{ route('user.create') }}"/>
    </p>

    <x-users.table :users="$users" />

    {{ $users->links('paginator') }}

@endsection
