@extends('layouts/ecl')

@section('content')

    <div class="ecl-row">
        <div class="ecl-col-12">
            <h1>Notices</h1>
            <ul>

                @foreach($notices as $notice)
                    <li>
                        <a href="/notice/{{$notice->id}}">{{$notice->title}}</a>
                        @if($notice->entities)
                            <ul>
                                @foreach($notice->entities as $entity)
                                    <li>
                                        {{$entity->name}}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    </li>
                @endforeach

                {{ $notices->links('paginator') }}
            </ul>
        </div>
    </div>

@endsection

