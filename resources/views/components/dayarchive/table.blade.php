@php use App\Models\DayArchive; @endphp
@props(['dayarchives' => null])

<style>
    .dayarchive-row:hover {
        background-color: #EBEBEB !important;
        cursor: pointer !important;
    }
</style>

<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">Date</th>
        <th class="ecl-table__header">Statements</th>
        <th class="ecl-table__header">File</th>
    </tr>
    </thead>
    <tbody class="ecl-table__body">
    @foreach($dayarchives as $dayarchive)

        <tr class="ecl-table__row dayarchive-row">
            <td class="ecl-table__cell" data-ecl-table-header="Date">{{$dayarchive->date}}</td>
            <td class="ecl-table__cell" data-ecl-table-header="Statements">{{$dayarchive->total}}</td>
            <td class="ecl-table__cell" data-ecl-table-header="File">
                <a download href="{{ route('dayarchive.download', [$dayarchive->date]) }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">{{basename($dayarchive->url)}}</span>
                    <svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="download"/>
                    </svg>
                </a>
            </td>
        </tr>

    @endforeach
    </tbody>
</table>


{{ $dayarchives->links('paginator') }}