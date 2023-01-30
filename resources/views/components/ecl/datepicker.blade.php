@props(['size' => 'l', 'required' => false, 'help' => false, 'label' => 'label', 'required' => false, 'name' => 'name', 'id' => 'id', 'value' => null])
<div class="ecl-form-group ecl-u-mb-l">
    <x-ecl.label :label=$label :required=$required :name=$name :for=$id />
    <x-ecl.help :help=$help />
    <div class="ecl-datepicker">
        <input type="text"
               autoComplete="off"
               data-ecl-datepicker-toggle=""
               data-ecl-auto-init="Datepicker"
               id="{{ $id }}"
               name="{{ $name }}"
               class="ecl-datepicker__field ecl-text-input ecl-text-input--{{ $size }}" @if($required)required="" @endif
               placeholder="DD-MM-YYYY"
               value="{{ old($name, $value) }}"/>
        <svg class="ecl-icon ecl-icon--s ecl-datepicker__icon" focusable="false" aria-hidden="true">
            <x-ecl.icon icon="calendar" />
        </svg>
    </div>
</div>