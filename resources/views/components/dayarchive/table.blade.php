@php use App\Models\DayArchive; @endphp
@php
    function human_filesize($bytes, $dec = 2): string {

        $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor == 0) $dec = 0;


        return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);

    }
@endphp
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
        <th class="ecl-table__header">File Full</th>
        <th class="ecl-table__header">Size Full</th>
        <th class="ecl-table__header">File Light</th>
        <th class="ecl-table__header">Size Light</th>
    </tr>
    </thead>
    <tbody class="ecl-table__body">
    @foreach($dayarchives as $dayarchive)

        <tr class="ecl-table__row dayarchive-row">
            <td class="ecl-table__cell" data-ecl-table-header="Date">{{$dayarchive->date}}</td>
            <td class="ecl-table__cell" data-ecl-table-header="Statements">{{$dayarchive->total}}</td>
            <td class="ecl-table__cell" data-ecl-table-header="File Full">
                <a download href="{{ route('dayarchive.download', [$dayarchive->date]) }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">{{basename($dayarchive->url)}}</span>
                    <svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="download"/>
                    </svg>
                </a>
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Size Full">{{human_filesize($dayarchive->size)}}</td>
            <td class="ecl-table__cell" data-ecl-table-header="File Light">
                <a download href="{{ route('dayarchive.download-light', [$dayarchive->date]) }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">{{basename($dayarchive->urllight)}}</span>
                    <svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true">
                        <x-ecl.icon icon="download"/>
                    </svg>
                </a>
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Size Light">{{human_filesize($dayarchive->sizelight)}}</td>
        </tr>

    @endforeach
    </tbody>
</table>


{{ $dayarchives->links('paginator') }}