@props(['platform' => null, 'options' => null ])

<x-ecl.textfield label="Name" name="name" id="name" required=true value="{{ $platform->name }}" />
<x-ecl.textfield label="DSA Common ID" name="dsa_common_id" id="dsa_common_id" value="{{ $platform->dsa_common_id }}" />

<x-ecl.radio label="Platform is VLOP?"
             name="vlop"
             id="vlop"
             :options="$options['vlops']"
             default="{{ $platform->vlop }}"
             required="true"
/>

<x-ecl.radio label="Platform is Onboarded?"
             name="onboarded"
             id="onboarded"
             :options="$options['onboardeds']"
             default="{{ $platform->onboarded }}"
             required="true"
/>

<x-ecl.radio label="Platform has Tokens?"
             name="has_tokens"
             id="has_tokens"
             :options="$options['has_tokens']"
             default="{{ $platform->has_tokens }}"
             required="true"
/>

<x-ecl.radio label="Platform has Statements?"
             name="has_statements"
             id="has_statements"
             :options="$options['has_statements']"
             default="{{ $platform->has_statements }}"
             required="true"
/>



