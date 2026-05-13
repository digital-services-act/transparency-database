@props([
    'name' => 'name',
    'error' => null
])

@php
    $message = $error
        ?? $errors->first($name);
@endphp

@if($message)
<div class="ecl-feedback-message">
    <svg class="ecl-icon ecl-icon--m ecl-feedback-message__icon" focusable="false" aria-hidden="true">
        <x-ecl.icon icon="error"/>
    </svg>
    {{ $message }}
</div>
@endif
