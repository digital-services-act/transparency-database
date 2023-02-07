@props(['statements' => null])
<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">Title</th>
        <th class="ecl-table__header">Creation date</th>
        <th class="ecl-table__header"></th>
        <th class="ecl-table__header"></th>
        <th class="ecl-table__header"></th>
    </tr>
    </thead>
    <tbody class="ecl-table__body">
    @foreach($statements as $statement)
        <tr class="ecl-table__row">
            <td class="ecl-table__cell"><a class="ecl-link" href="{{ route('statement.show', [$statement]) }}">{{ $statement->title }}</a></td>
            <td class="ecl-table__cell">{{ $statement->created_at }}</td>
            <td class="ecl-table__cell"></td>
            <td class="ecl-table__cell"></td>
            <td class="ecl-table__cell"></td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $statements->links('paginator') }}
