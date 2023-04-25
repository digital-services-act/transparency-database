@props(['statements' => null])
<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">Creator</th>
        <th class="ecl-table__header">UUID</th>
        <th class="ecl-table__header">Platform Type</th>
        <th class="ecl-table__header">Creation date</th>
        <th class="ecl-table__header"></th>
    </tr>
    </thead>
    <tbody class="ecl-table__body">
    @foreach($statements as $statement)
        <tr class="ecl-table__row">
            <td class="ecl-table__cell">{{$statement->user->platform?->name}}</td>
            <td class="ecl-table__cell"><a class="ecl-link" href="{{ route('statement.show', [$statement]) }}">{{ $statement->uuid }}</a></td>
            <td class="ecl-table__cell">{{ \App\Models\Platform::PLATFORM_TYPES[$statement->user->platform?->type] ?? '' }}</td>
            <td class="ecl-table__cell">{{ $statement->created_at }}</td>
            <td class="ecl-table__cell">
                @can('administrate')
                    edit / delete
                @endcan
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $statements->links('paginator') }}
