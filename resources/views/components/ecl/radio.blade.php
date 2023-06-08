@props(['label' => 'label', 'id' => 'id', 'name' => 'name', 'required' => false, 'help' => null, 'options' => [], 'default' => null])

<div class="ecl-form-group ecl-u-mb-l" id="div_{{$id}}">
<fieldset class="ecl-form-group ecl-u-mb-l" aria-describedby="helper-id-1">

    <x-ecl.label type="legend" :label=$label :for=$id :name=$name :required=$required />

    <x-ecl.help :help=$help />
    <x-ecl.error-feedback :name=$name />

    @foreach($options as $option)
        <div class="ecl-radio">
            <input type="radio" id="{{ $id }}-{{ $loop->iteration }}" name="{{ $name }}" class="ecl-radio__input" value="{{ $option['value'] }}" @if(old($name, $default) == $option['value'])checked="" @endif required="" aria-describedby="{{ $name }}-{{ $loop->iteration }}" />
            <label class="ecl-radio__label" for="{{ $id }}-{{ $loop->iteration }}"><span class="ecl-radio__box"><span class="ecl-radio__box-inner"></span></span><span class="ecl-radio__text">{{ $option['label'] }}</span></label>
{{--            <div class="ecl-radio__help" id="helper-1">Help text for an option</div>--}}
        </div>
    @endforeach

</fieldset>
</div>