@if(auth()->user())
    Logged in as
    <ul>
        <li>
            {{auth()->user()}}
        </li>
        <li>
            {{auth()->user()->getAttribute('firstName')}} {{auth()->user()->getAttribute('lastName')}}
        </li>
        <li>
            {{auth()->user()->getAttribute('email')}}
        </li>
    </ul>

@endif
Profile Page<br/>
<br/>
<a href="/">Homepage</a>

