@props(['label' => 'label', 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'size' => 'l', 'placeholder' => '', 'value' => null, 'justlabel' => false, 'readonly' => false])

<div class="ecl-form-group ecl-u-mb-2xl" id="div_{{$id}}">
    <x-ecl.label :label="$label" :for="$id" :name="$name" :required="$required" :justlabel="$justlabel" />
    <x-ecl.help :help="$help" />
    <x-ecl.error-feedback :name="$name" />
    <input @if($readonly) readonly @endif type="text" name="{{ $name }}" id="{{ $id  }}" class="ecl-text-input ecl-text-input--{{ $size }} @error($name)ecl-text-input--invalid @enderror" placeholder="{{ $placeholder }}" value="{{old($name, $value)}}"/>
</div>

