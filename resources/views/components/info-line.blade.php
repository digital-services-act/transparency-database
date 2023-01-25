@props(['title' , 'content'])

<div class="ecl-row">
    <div class="ecl-col-4">
        <div class="ecl-u-pa-xs ecl-u-type-color-blue ecl-u-type-l ecl-u-type-bold">
            {{$title}}
        </div>
    </div>
    <div class="ecl-col-8">
        <div class="ecl-u-pa-xs ecl-u-type-color-black ecl-u-type-l">
            {{$content}}
        </div>
    </div>
</div>
