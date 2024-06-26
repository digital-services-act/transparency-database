<style>
    .hover-container {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .hover-text {
        visibility: hidden;
        width: 300px;
        background-color: #1b3f7e;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 5px;
        position: absolute;
        z-index: 1;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .hover-container:hover .hover-text {
        visibility: visible;
        opacity: 1;
    }
</style>

@php
    $randomID = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(10/strlen('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')))), 1, 10);
@endphp

<span class="hover-container" onmousemove="showHoverText(event)">
    <svg class="ecl-icon ecl-icon--m ecl-link__icon ecl-u-type-color-primary ecl-u-ml-s" focusable="false" aria-hidden="true" style="vertical-align: middle;">
        <x-ecl.icon icon="information"/>
    </svg>
    <span class="hover-text" id="{{$randomID}}">
        {{ $hoverText }}
    </span>
</span>

<script>
    function showHoverText(event) {
        const hoverText = document.getElementById({{$randomID}});
        hoverText.style.left = `${event.clientX + 10}px`;
        hoverText.style.top = `${event.clientY}px`;
    }
</script>
