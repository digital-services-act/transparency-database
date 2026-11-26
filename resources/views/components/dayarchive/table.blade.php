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
@props(['dayarchives' => null])

<style>
    .dayarchive-row:hover {
        background-color: #EBEBEB !important;
    }

    .dayarchive-table {
        table-layout: auto;
    }

    .dayarchive-table .ecl-table__header {
        min-width: 0;
    }
</style>

<div class="ecl-table-responsive">
    <table class="ecl-table ecl-table--zebra dayarchive-table">
        <thead class="ecl-table__head">
        <tr class="ecl-table__row">
            <th class="ecl-table__header" scope="col">Date</th>
            <th class="ecl-table__header" scope="col">Statements of reasons</th>
            <th class="ecl-table__header" scope="col">Full</th>
            <th class="ecl-table__header" scope="col">Size</th>
            <th class="ecl-table__header" scope="col">Light</th>
            <th class="ecl-table__header" scope="col">Size</th>
            @can('administrate')
                <th class="ecl-table__header" scope="col">Parquet</th>
            @endcan
        </tr>
        </thead>
    <tbody class="ecl-table__body" style="font-size: smaller;">
    @foreach($dayarchives as $dayarchive)

        <tr class="ecl-table__row dayarchive-row">
            <td class="ecl-table__cell" data-ecl-table-header="Date">{{$dayarchive->date->format('Y-m-d')}}</td>
            <td class="ecl-table__cell" data-ecl-table-header="Statements of reasons">
                @aif($dayarchive->total)
                @can('administrate')
                    @if(is_null($dayarchive->platform_id) && $dayarchive->parquet_url && !is_null($dayarchive->total_parquet))
                        / @aif($dayarchive->total_parquet)
                    @endif
                @endcan
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Full">
                <a href="{{ route('dayarchive.download', ['dayArchive' => $dayarchive->id, 'type' => 'full']) }}"
                   title="Download full archive"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">zip</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
                &nbsp;&nbsp;&nbsp;
                <a href="{{ route('dayarchive.download', ['dayArchive' => $dayarchive->id, 'type' => 'sha1']) }}"
                   title="Download SHA1 checksum"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">sha1</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Size">
                csv:&nbsp;{{human_filesize($dayarchive->size)}}<br/>
                zip:&nbsp;{{human_filesize($dayarchive->zipsize)}}&nbsp
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Light">
                <a href="{{ route('dayarchive.download', ['dayArchive' => $dayarchive->id, 'type' => 'light']) }}"
                   title="Download light archive"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">zip</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
                &nbsp;&nbsp;&nbsp;
                <a href="{{ route('dayarchive.download', ['dayArchive' => $dayarchive->id, 'type' => 'sha1light']) }}"
                   title="Download SHA1 checksum"
                   class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                    <span class="ecl-link__label">sha1</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                </a>
            </td>
            <td class="ecl-table__cell" data-ecl-table-header="Size">
                csv:&nbsp;{{human_filesize($dayarchive->sizelight)}}<br>
                zip:&nbsp;{{human_filesize($dayarchive->ziplightsize)}}
            </td>
            @can('administrate')
                <td class="ecl-table__cell" data-ecl-table-header="Parquet">
                    @if(is_null($dayarchive->platform_id) && $dayarchive->parquet_url)
                        <a href="{{ route('dayarchive.download.parquet', ['dayArchive' => $dayarchive->id]) }}"
                           title="Download Parquet archive"
                           class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                            <span class="ecl-link__label">parquet</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                        </a>
                        @if(!is_null($dayarchive->parquet_size))
                            &nbsp;{{human_filesize($dayarchive->parquet_size)}}
                        @endif
                        @if($dayarchive->parquet_sha1_url)
                            <br>
                            <a href="{{ route('dayarchive.download.parquet.sha1', ['dayArchive' => $dayarchive->id]) }}"
                               title="Download Parquet SHA1 checksum"
                               class="ecl-link ecl-link--standalone ecl-link--icon ecl-link--icon-after">
                                <span class="ecl-link__label">sha1</span><svg class="ecl-icon ecl-icon--fluid ecl-link__icon" focusable="false" aria-hidden="true"><x-ecl.icon icon="download"/></svg>
                            </a>
                        @endif
                    @endif
                </td>
            @endcan
        </tr>

    @endforeach
        </tbody>
    </table>
</div>


{{ $dayarchives->links('paginator') }}
