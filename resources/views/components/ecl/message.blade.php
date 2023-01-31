@props(['type' => 'info', 'icon' => 'information', 'title' => 'title', 'message' => 'message'])
<div class="ecl-message ecl-message--{{ $type }} ecl-u-mb-l" data-ecl-message="" role="alert" data-ecl-auto-init="Message">
    <svg class="ecl-icon ecl-icon--l ecl-message__icon" focusable="false" aria-hidden="true">
        <x-ecl.icon icon="{{ $icon }}" />
    </svg>
    <div class="ecl-message__content">
        <button class="ecl-button ecl-button--ghost ecl-message__close" type="button" data-ecl-message-close="">
            <span class="ecl-button__container">
                <span class="ecl-button__label" data-ecl-label="true">Close</span>
                <svg class="ecl-icon ecl-icon--xs ecl-button__icon ecl-button__icon--after" focusable="false" aria-hidden="true" data-ecl-icon="">
                    <x-ecl.icon icon="close-filled" />
                </svg>
            </span>
        </button>
        <div class="ecl-message__title">{{ $title }}</div>
        <div class="ecl-message__description">
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