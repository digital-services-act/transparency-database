@props([
    'size' => 'm',
    'required' => false,
    'help' => false,
    'label' => 'label',
    'name' => 'name',
    'id' => 'id',
    'value' => null,
    'justlabel' => false,
    'disableFuture' => false,
    'invalid' => false,
    'disabled' => false,
    'autoinit' => true,
    'placeholder' => 'DD-MM-YYYY',
    'firstDayOfWeek' => 1,
])

@php
    $rawValue = old($name, $value);
    $defaultValue = '';

    if ($rawValue instanceof \DateTimeInterface) {
        $defaultValue = $rawValue->format('Y-m-d');
    } elseif (is_string($rawValue) && $rawValue !== '') {
        foreach (['Y-m-d', 'd-m-Y'] as $format) {
            try {
                $date = \Illuminate\Support\Carbon::createFromFormat('!' . $format, $rawValue);

                if ($date && $date->format($format) === $rawValue) {
                    $defaultValue = $date->format('Y-m-d');
                    break;
                }
            } catch (\Exception) {
                // Leave invalid submitted values empty in the datepicker.
            }
        }
    }
@endphp

<div class="ecl-form-group ecl-u-mb-2xl">
    <x-ecl.label :label=$label :required=$required :name=$name :for=$id :justlabel="$justlabel" />
    <x-ecl.help :help=$help />
    <div
        class="ecl-datepicker ecl-datepicker--{{ $size }} {{ $invalid ? 'ecl-datepicker--invalid' : '' }}"
        @if ($autoinit) data-ecl-auto-init="Datepicker" @endif
        @if ($invalid) invalid @endif
        @if ($defaultValue !== '') data-value="{{ $defaultValue }}" @endif
        data-placeholder="{{ $placeholder }}"
        data-ecl-datepicker-toggle
    >
        <duet-date-picker
            identifier="{{ $id }}"
            name="{{ $name }}"
            first-day-of-week="{{ $firstDayOfWeek }}"
            @if ($defaultValue !== '') value="{{ $defaultValue }}" @endif
            @if ($disableFuture) max="{{ now()->toDateString() }}" @endif
            @if ($disabled) disabled @endif
            @if ($required) required @endif
        ></duet-date-picker>
    </div>
    <x-ecl.error-feedback :name="$name" />
</div>
