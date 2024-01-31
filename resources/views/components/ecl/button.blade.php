@props(['label' => 'label', 'priority' => 'primary', 'type' => 'submit', 'id' => false, 'class' => null, 'style' => null])
<button @if($id)id="{{ $id }}" @endif class="ecl-button ecl-button--{{ $priority }} @if($class){{$class}} @endif" type="{{ $type }}" @if($style)style="{{$style}}"@endif>{{ $label }}</button>
