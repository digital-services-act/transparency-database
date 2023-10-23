<x-mail::message>
# Feedback received

We received this feedback from the user {{auth()->user()->getAttribute('name')}} ({{auth()->user()->getAttribute('email')}})
<pre style="font-style: italic; margin-left: 20px">
{{$feedback}}
</pre>
Thanks,<br>
DSA Transparency Database Robot
</x-mail::message>
