@props(['label' => 'label', 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'size' => 'l', 'placeholder' => '', 'value' => null])

<div class="ecl-form-group ecl-u-mb-l">
    <x-ecl.label :label=$label :for=$id :name=$name :required=$required />
    <x-ecl.help :help=$help />
    <x-ecl.error-feedback :name=$name />
    <input type="text" name="{{ $name }}" id="{{ $id  }}" class="ecl-text-input ecl-text-input--{{ $size }} @error($name)ecl-text-input--invalid @enderror" placeholder="{{ $placeholder }}" value="{{old($name, $value)}}"/>
</div>