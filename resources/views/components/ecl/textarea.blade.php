@props(['label' => 'label', 'required' => false, 'help' => false, 'name' => 'name', 'id' => 'id', 'size' => 'l', 'placeholder' => ''])

<div class="ecl-form-group ecl-u-mb-l" id="div_{{$id}}">
    <x-ecl.label :label=$label :for=$id :name=$name :required=$required />
    <x-ecl.help :help=$help />
    <x-ecl.error-feedback :name=$name />
    <textarea rows="4" type="text" name="{{ $name }}" id="{{ $id  }}" class="ecl-text-area ecl-text-area--{{ $size }} @error($name)ecl-text-area--invalid @enderror" placeholder="{{ $placeholder }}">{{old($name)}}</textarea>
</div>
