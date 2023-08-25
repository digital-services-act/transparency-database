@extends(backpack_view('blank'))

@php
    if (backpack_theme_config('show_getting_started')) {
        $widgets['before_content'][] = [
            'type'        => 'view',
            'view'        => backpack_view('inc.getting_started'),
        ];
    } else {


        $widgets['before_content'][] = [
    'type'         => 'alert',
    'class'        => 'alert alert-danger mb-2',
    'heading'      => 'Important information!',
    'content'      => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Corrupti nulla quas distinctio veritatis provident mollitia error fuga quis repellat, modi minima corporis similique, quaerat minus rerum dolorem asperiores, odit magnam.',
    'close_button' => true, // show close button or not
];
    }
@endphp

@section('content')
    Welcome to the DSA Admin Panel
@endsection
