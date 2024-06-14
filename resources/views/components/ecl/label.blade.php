@props(['label' => 'label', 'required' => false, 'for' => 'for', 'name' => 'name', 'type' => 'label', 'justlabel' => false, 'info_text'=>false])
<div class="ecl-u-type-paragraph" style="max-width:none !important; ">
    <{{ $type }} for
    ="{{ $for }}" style="white-space: normal !important; display: inline !important;" class="ecl-form-label @error($name)ecl-form-label--invalid @enderror">{!! $label !!}
    @if(!$justlabel)
        @if($required)
            <span class="ecl-form-label__required"> *</span>
        @else
            <span class="ecl-form-label__optional"> (optional)</span>
        @endif
    @endif
    @if($info_text)
        <x-hover-text :hoverText="$info_text"/>
    @endif
</{{ $type }}>

    </div>
