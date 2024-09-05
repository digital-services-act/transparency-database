@props(['users' , 'content', 'delete' => false])
<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">User</th>
        <th class="ecl-table__header">Platform</th>
        <th class="ecl-table__header">Roles</th>
        <th class="ecl-table__header">Has Token ?</th>

        <th class="ecl-table__header" width="10%">Actions</th>
    </tr>
    </thead>
    <tbody class="ecl-table__body">
    @foreach($users as $user)
        <tr class="ecl-table__row">
            <td class="ecl-table__cell" data-ecl-table-header="User">{{ $user->email }}</td>
            <td class="ecl-table__cell" data-ecl-table-header="Platform">{{ $user->platform->name ?? '' }}</td>
            <td class="ecl-table__cell" data-ecl-table-header="Roles">{{ implode(", ", $user->roles->pluck('name')->toArray()) }}</td>
            <td class="ecl-table__cell" data-ecl-table-header="Has Token ?">{{ $user->hasValidApiTokenHuman()  }}</td>
            <td class="ecl-table__cell" data-ecl-table-header="Actions">

                <button class="ecl-u-d-inline ecl-u-f-l ecl-u-mr-m ecl-button ecl-button--secondary" onclick="document.location.href = '{{ route('user.edit', ['user' => $user, 'returnto' => request()->fullUrl()]) }}'">edit</button>

                @if($delete)
                <form action="{{ route('user.destroy', ['user' => $user, 'returnto' => request()->fullUrl()]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="submit" class="ecl-u-d-inline ecl-u-f-l ecl-button ecl-button--secondary" value="delete" />
                </form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
