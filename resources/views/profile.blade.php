@if(auth()->user())
    Logged in as
    <ul>
        <li>
            {{auth()->user()}}
        </li>
        <li>
            {{auth()->user()->getAttribute('name')}}
        </li>
        <li>
            {{auth()->user()->getAttribute('email')}}
        </li>

        <li>
            {{print_r(session()->all())}}
        </li>
    </ul>

@endif
Profile Page<br/>
<br/>
<a href="/">Homepage</a>

