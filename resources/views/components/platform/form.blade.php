@props(['platform' => null, 'options' => null ])

<x-ecl.textfield label="Name" name="name" id="name" required=true value="{{ $platform->name }}" />
<x-ecl.textfield label="DSA Common ID" name="dsa_common_id" id="dsa_common_id" value="{{ $platform->dsa_common_id }}" />

<x-ecl.radio label="Platform is VLOP"
             name="vlop"
             id="vlop"
             :options="$options['vlops']"
             default="{{ $platform->vlop }}"
             required="true"
/>



