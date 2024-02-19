@props(['count' => 0, 'label' => 'label'])
<span @class([
'ecl-label',
'ecl-label--high' => $count > 0,
'ecl-label--low' => $count == 0
])
>{{$label}}: {{$count}}</span>
