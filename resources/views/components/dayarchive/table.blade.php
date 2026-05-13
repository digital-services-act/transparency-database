@php use App\Models\DayArchive; @endphp
@php
    if (!function_exists('human_filesize')) {
        function human_filesize($bytes, $dec = 2) {
            $size   = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
            $factor = floor((strlen($bytes) - 1) / 3);
            if ($factor == 0) $dec = 0;
            return sprintf("%.{$dec}f %s", $bytes / (1024 ** $factor), $size[$factor]);
        }
    }
@endphp
@props(['dayarchives' => null, 'reindexing' => false])

<style>
    .dayarchive-row:hover {
        background-color: #EBEBEB !important;
    }
</style>

<table class="ecl-table ecl-table--zebra">
    <thead class="ecl-table__head">
    <tr class="ecl-table__row">
        <th class="ecl-table__header">Date</th>
        @if(!$reindexing)<th class="ecl-table__header">Statements of Reasons</th>@endif
        <th class="ecl-table__header">Full</th>
        <th class="ecl-table__header">Size</th>
        <th class="ecl-table__header">Light</th>
        <th class="ecl-table__header">Size</th>
    </tr>
    </thead>
    <tbody class="ecl-table__body" style="font-size: smaller;">
    @foreach($dayarchives as $dayarchive)

        <tr class="ecl-table__row dayarchive-row">
            <td class="ecl-table__cell" data-ecl-table-header="Date">{{$dayarchive->date->format('Y-m-d')}}</td>
            @if(!$reindexing)<td class="ecl-table__cell" data-ecl-table-header="Statements of Reasons">@aif($dayarchive->total)</td>@endif
            <td class="ecl-table__cell" data-ecl-table-header="Full">
                <a href="{{ $dayarchive->url }}"
                   title="{{ $dayarchive->url }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">zip</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
                &nbsp;&nbsp;&nbsp;
                <a download href="{{ $dayarchive->sha1url }}"
                   title="{{ $dayarchive->sha1url }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">sha1</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Size">
                csv:&nbsp;{{human_filesize($dayarchive->size)}}<br/>
                zip:&nbsp;{{human_filesize($dayarchive->zipsize)}}&nbsp
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Light">
                <a download href="{{ $dayarchive->urllight }}"
                   title="{{ $dayarchive->urllight }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">zip</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
                &nbsp;&nbsp;&nbsp;
                <a download href="{{ $dayarchive->sha1urllight }}"
                   title="{{ $dayarchive->sha1urllight }}"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">sha1</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Size">
                csv:&nbsp;{{human_filesize($dayarchive->sizelight)}}<br>
                zip:&nbsp;{{human_filesize($dayarchive->ziplightsize)}}
            </td>
        </tr>

    @endforeach
    </tbody>
</table>


{{ $dayarchives->links('paginator') }}
