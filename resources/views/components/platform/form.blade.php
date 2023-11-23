@props(['platform' => null, 'options' => null ])

<x-ecl.textfield label="Name" name="name" id="name" required=true value="{{ $platform->name }}" />
<x-ecl.textfield label="Url" name="url" id="url" required=true value="{{ $platform->url }}" />

<x-ecl.radio label="Platform is VLOP"
             name="vlop"
             id="vlop"
             :options="$options['vlops']"
             default="{{ $platform->vlop }}"
             required="true"
/>



