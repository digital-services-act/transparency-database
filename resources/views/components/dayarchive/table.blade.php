@php use App\Models\DayArchive; @endphp
@php
    function human_filesize($bytes, $dec = 2): string {

        $size   = array('b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb');
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor == 0) $dec = 0;


        return sprintf("%.{$dec}f%s", $bytes / (1024 ** $factor), $size[$factor]);

    }
@endphp
@props(['dayarchives' => null])

<style>
    .dayarchive-row:hover {
        background-color: #EBEBEB !important;
    }
</style>

<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">Date</th>
        <th class="ecl-table__header">Statements of Reasons</th>
        <th class="ecl-table__header">Full</th>
        <th class="ecl-table__header">Size</th>
        <th class="ecl-table__header">Light</th>
        <th class="ecl-table__header">Size</th>
    </tr>
    </thead>
    <tbody class="ecl-table__body" style="font-size: smaller;">
    @foreach($dayarchives as $dayarchive)

        <tr class="ecl-table__row dayarchive-row">
            <td class="ecl-table__cell" data-ecl-table-header="Date">{{$dayarchive->date}}</td>
            <td class="ecl-table__cell" data-ecl-table-header="Statements of Reasons">@aif($dayarchive->total)</td>
            <td class="ecl-table__cell" data-ecl-table-header="File">
                <a href="{{ $dayarchive->url }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">zip</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
                &nbsp;&nbsp;&nbsp;
{{--                <a href="{{ $dayarchive->sha1url }}" class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">--}}
{{--                    <span class="ecl-link__label">sha1</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>--}}
{{--                </a>--}}
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Size">
{{--                {{human_filesize($dayarchive->size)}}&nbsp;.csv<br/>--}}
                {{human_filesize($dayarchive->zipsize)}}&nbsp;.zip
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="File">
                <a download href="{{ $dayarchive->urllight }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">zip</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
                &nbsp;&nbsp;&nbsp;
{{--                <a href="{{ $dayarchive->sha1urllight }}" class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">--}}
{{--                    <span class="ecl-link__label">sha1</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>--}}
{{--                </a>--}}
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Size">
{{--                {{human_filesize($dayarchive->sizelight)}}&nbsp;.csv<br />--}}
                {{human_filesize($dayarchive->ziplightsize)}}&nbsp;.zip
            </td>
        </tr>

    @endforeach
    </tbody>
</table>


{{ $dayarchives->links('paginator') }}