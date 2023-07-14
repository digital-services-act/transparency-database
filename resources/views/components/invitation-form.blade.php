@props(['invitation' => null, 'options' => null])

<x-ecl.textfield label="Email" type="email" name="email" id="email" required=true value="{{ $invitation->email }}" />

<x-ecl.select label="Platform"
              name="platform_id"
              id="platform_id" default="{{ $invitation->platform_id }}"
              :options="$options['platforms']"
              :required="true" />



