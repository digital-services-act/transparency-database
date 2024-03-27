@props(['type' => 'info', 'icon' => 'information', 'title' => 'title', 'message' => 'message', 'close' => true])
<div class="ecl-notification ecl-notification--{{ $type }} ecl-u-mb-l" data-ecl-notification="" role="alert" data-ecl-auto-init="Notification">
    <svg class="ecl-icon ecl-icon--l ecl-notification__icon" focusable="false" aria-hidden="false" role="img">
        <title>{{ $title }}</title>
        <x-ecl.icon icon="{{ $icon }}" />
    </svg>
    <div class="ecl-notification__content">
        @if($close)
        <button class="ecl-button ecl-button--tertiary ecl-notification__close ecl-button--icon-only" type="button" data-ecl-notification-close="">
            <span class="ecl-button__container">
                <span class="ecl-button__label" data-ecl-label="true">Close</span>
                <svg class="ecl-icon ecl-icon--xs ecl-button__icon ecl-button__icon--after" focusable="false" aria-hidden="true" data-ecl-icon="">
                    <x-ecl.icon icon="close-filled" />
                </svg>
            </span>
        </button>
        @endif
        <div class="ecl-notification__title">{{ $title }}</div>
        <div class="ecl-notification__description">
        @if(!is_array($message))
            {!! $message !!}
        @else
            <ul class="ecl-unordered-list">
                <li class="ecl-unordered-list__item">
                    {!! implode('</li><li class="ecl-unordered-list__item">', $message) !!}
                </li>
            </ul>
        @endif
        </div>
    </div>
</div>

