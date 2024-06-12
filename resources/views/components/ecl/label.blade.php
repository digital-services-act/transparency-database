@props(['label' => 'label', 'required' => false, 'for' => 'for', 'name' => 'name', 'type' => 'label', 'justlabel' => false, 'info_text'=>false])
<{{ $type }} for="{{ $for }}" class="ecl-form-label @error($name)ecl-form-label--invalid @enderror">{!! $label !!} @if(!$justlabel)@if($required)<span class="ecl-form-label__required"> *</span>@else<span class="ecl-form-label__optional"> (optional)</span>@endif @endif</{{ $type }}>

