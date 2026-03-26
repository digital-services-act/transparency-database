@props([
    'size' => 'l',
    'required' => false,
    'help' => false,
    'label' => 'label',
    'required' => false,
    'name' => 'name',
    'id' => 'id',
    'value' => null,
    'justlabel' => false,
    'disableFuture' => false,
    'invalid' => false,
])
<div class="ecl-form-group ecl-u-mb-2xl">
    <x-ecl.label :label=$label :required=$required :name=$name :for=$id :justlabel="$justlabel" />
    <x-ecl.help :help=$help />
    <div class="ecl-datepicker {{ $invalid ? 'ecl-datepicker--invalid' : '' }}">
        <input
            id="{{ $id }}"
            class="ecl-datepicker__field ecl-text-input ecl-text-input--{{ $size }} {{ $invalid ? 'ecl-text-input--invalid' : '' }}"
            type="text"
            autoComplete="off"
            data-ecl-datepicker-toggle=""
            name="{{ $name }}"
            @if ($required) required="" @endif
            placeholder="DD-MM-YYYY"
            value="{{ old($name, $value) }}"
        />
        <svg class="ecl-icon ecl-icon--s ecl-datepicker__icon" focusable="false" aria-hidden="true">
            <x-ecl.icon icon="calendar" />
        </svg>
    </div>
    <x-ecl.error-feedback :name="$name" />
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var field = document.getElementById('{{ $id }}');
        if (field) {
            new Pikaday({
                field: field,
                format: 'DD-MM-YYYY',
                theme: 'ecl-datepicker-theme',
                toString(date, format) {
                    return moment(date).format(format);
                },
                parse(dateString, format) {
                    return moment(dateString, format).toDate();

                },
                setDefaultDate: false,
                defaultDate: null,
                minDate: null,
                @if ($disableFuture)
                maxDate: new Date(),
                @endif
            });
        }
    });
</script>
