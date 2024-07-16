@props(['text'=>'','select_item'=>'Select an Item', 'justlabel' => false, 'select_all' => 'Select All', 'label' => 'label', 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'options' => [], 'size' => 'l', 'default' => []])
@php
    $randomID = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(10/strlen('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')))), 1, 10);
@endphp



<div class="ecl-popover" data-ecl-auto-init="Popover"><a href="#{{$randomID}}"
                                                         class="ecl-link ecl-link--standalone ecl-link--icon ecl-popover__toggle"
                                                         aria-controls="{{$randomID}}" data-ecl-popover-toggle
                                                         aria-expanded="false" aria-label="Popover toggle">
        <svg class="ecl-icon ecl-icon--m ecl-link__icon" focusable="false" aria-hidden="true">
            <x-ecl.icon icon="information"/>
        </svg>
    </a>
    <div id="{{$randomID}}" class="ecl-popover__container" hidden>
        <div class="ecl-popover__scrollable">
            <button class="ecl-button ecl-button--tertiary ecl-popover__close ecl-button--icon-only" type="button"
                    data-ecl-popover-close><span class="ecl-button__container"><span class="ecl-button__label"
                                                                                     data-ecl-label="true">Close</span><svg
                        class="ecl-icon ecl-icon--m ecl-button__icon" focusable="false" aria-hidden="true"
                        data-ecl-icon>
                          <x-ecl.icon icon="close"/>
            </svg></span></button>
            <div class="ecl-popover__content">{{$text}}
            </div>
        </div>
    </div>
</div>
