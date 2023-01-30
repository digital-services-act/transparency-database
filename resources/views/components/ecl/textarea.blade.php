@props(['label' => 'label', 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'size' => 'l', 'placeholder' => ''])

<div class="ecl-form-group ecl-u-mb-l">
    <label for="{{ $id }}" class="ecl-form-label @error($name)ecl-form-label--invalid @enderror">{{ $label }}@if($required)<span class="ecl-form-label__required"> *</span>@else<span class="ecl-form-label__optional"> (optional)</span>@endif</label>
    @if($help)<div class="ecl-help-block">{{ $help }}</div>@endif
    @error($name)
    <div class="ecl-feedback-message">
        <svg class="ecl-icon ecl-icon--m ecl-feedback-message__icon" focusable="false" aria-hidden="true">
            <x-ecl.icon icon="error"/>
        </svg>
        {{ $message }}
    </div>
    @enderror
    <textarea rows="4" type="text" name="{{ $name }}" id="{{ $id  }}" class="ecl-text-area ecl-text-area--{{ $size }} @error($name)ecl-text-area--invalid @enderror" placeholder="{{ $placeholder }}">{{old($name)}}</textarea>
</div>