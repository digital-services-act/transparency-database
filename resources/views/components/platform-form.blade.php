@props(['platform' => null, 'options' => null ])

<x-ecl.textfield label="Name" name="name" id="name" required=true value="{{ $platform->name }}" />
<x-ecl.textfield label="Url" name="url" id="url" required=true value="{{ $platform->url }}" />


<x-ecl.select label="Type"
              name="type"
              id="type" default="{{ $platform->type }}"
              :options="$options['platform_types']"
              required="true" />

