<style>
    .hover-container {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .hover-text {
        visibility: hidden;
        width: 200px;
        background-color: black;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 10px;
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

<div class="hover-container" onmousemove="showHoverText(event)">
    <svg class="ecl-icon ecl-icon--m ecl-link__icon ecl-u-type-color-primary" focusable="false" aria-hidden="true" style="vertical-align: bottom">
        <x-ecl.icon icon="information"/>
    </svg>
    <div class="hover-text" id="{{$randomID}}">
        {{ $hoverText }}
    </div>
</div>

<script>
    function showHoverText(event) {
        const hoverText = document.getElementById({{$randomID}});
        hoverText.style.left = `${event.clientX + 10}px`;
        hoverText.style.top = `${event.clientY}px`;
    }
</script>
