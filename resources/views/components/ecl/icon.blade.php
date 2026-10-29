@props(['icon' => 'global', 'set' => 'b6764719'])
@php
    $webToolsIcon = [
        'success' => 'success-filled',
    ][$icon] ?? $icon;
@endphp
<g data-wt-icon="{{ $webToolsIcon }}"></g>
