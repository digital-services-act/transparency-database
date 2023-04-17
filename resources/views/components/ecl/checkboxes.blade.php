@props(['label' => 'label', 'id' => 'id', 'name' => 'name', 'required' => false, 'help' => null, 'options' => [], 'default' => [], 'justlabel' => false])

<fieldset class="ecl-form-group ecl-u-mb-l" aria-describedby="helper-id-1">

    <x-ecl.label type="legend" :label=$label :for=$id :name=$name :required=$required :justlabel=$justlabel/>

    <x-ecl.help :help=$help />
    <x-ecl.error-feedback :name=$name />

    @foreach($options as $option)
        <x-ecl.checkbox id="{{ $id }}-{{ $loop->iteration }}" label="{{ $option['label'] }}" name="{{ $name }}[]" value="{{ $option['value'] }}" :checked="in_array($option['value'], $default)" />
    @endforeach

</fieldset>