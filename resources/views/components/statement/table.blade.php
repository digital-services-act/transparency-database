@props(['statements' => null])
<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">Creator</th>
        <th class="ecl-table__header">UUID</th>
        <th class="ecl-table__header">Content Type</th>
        <th class="ecl-table__header">Creation date</th>

    </tr>
    </thead>
    <tbody class="ecl-table__body">
    @foreach($statements as $statement)
        <tr class="ecl-table__row">
            <td class="ecl-table__cell">{{$statement->platform?->name}}</td>
            <td class="ecl-table__cell"><a class="ecl-link" href="{{ route('statement.show', [$statement]) }}">{{ $statement->uuid }}</a></td>
            <td class="ecl-table__cell">{{ \App\Models\Statement::CONTENT_TYPES[$statement->content_type] ?? '' }}</td>
            <td class="ecl-table__cell">{{ $statement->created_at->format('Y-m-d') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $statements->links('paginator') }}