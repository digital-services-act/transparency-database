@php use App\Models\Statement; @endphp
@props(['statements' => null])
<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">Platform</th>
        <th class="ecl-table__header">Restrictions</th>
        <th class="ecl-table__header">Category</th>
        <th class="ecl-table__header">Creation Date</th>
        <th class="ecl-table__header">&nbsp;</th>

    </tr>
    </thead>
    <tbody class="ecl-table__body">
    @foreach($statements as $statement)
        <tr class="ecl-table__row">
            <td class="ecl-table__cell">{{$statement->platform_name}}</td>
            <td class="ecl-table__cell">{{$statement->restrictions()}}</td>
            <td class="ecl-table__cell">{{Statement::STATEMENT_CATEGORIES[$statement->category]}}</td>
            <td class="ecl-table__cell">{{ $statement->created_at->format('Y-m-d') }}</td>
            <td class="ecl-table__cell">
                <a class="ecl-link ecl-link--default ecl-link--icon ecl-link--icon-after"
                   href="{{ route('statement.show', [$statement]) }}">
                    <svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="file" />
                    </svg>
                </a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $statements->links('paginator') }}