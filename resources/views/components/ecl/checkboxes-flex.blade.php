@props(['label' => 'label', 'id' => 'id', 'name' => 'name', 'required' => false, 'help' => null, 'options' => [], 'default' => [], 'justlabel' => false])

<fieldset class="ecl-form-group ecl-u-mb-l">

    <div>
    <x-ecl.label type="legend" :label="$label" :for="$id" :name="$name" :required="$required" :justlabel="$justlabel"/>

    <x-ecl.help :help="$help" />
    <x-ecl.error-feedback :name="$name" />

    <div class="demo-container ecl-u-d-flex ecl-u-flex-wrap ecl-u-mt-m">
        @if(is_array($options) && is_array($default))
            @foreach($options as $option)
                <div class="ecl-u-mr-m">
                    <x-ecl.checkbox id="{{ $id }}-{{ $loop->iteration }}" label="{{ $option['label'] }}" name="{{ $name }}[]" value="{{ $option['value'] }}" :checked="in_array($option['value'], $default)" />
                </div>
            @endforeach
        @endif
    </div>

</fieldset>