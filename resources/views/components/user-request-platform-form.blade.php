@props(['options' => null ])


<x-ecl.select label="Platform"
              name="platform"
              help="Please indicate to us which platform that you belong to."
              id="platform" default="{{ old('platform') }}"
              :options="$options['platforms']"
              required="true" />

