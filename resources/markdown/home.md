# Welcome to the DSA Transparency Database!

<div class="ecl-u-mb-l" style="width: 100% !important color: #404040 !important;
font: normal normal 400 1rem/1.5rem arial,sans-serif !important;">

The Digital Services Act (DSA), obliges providers of hosting services to inform their users of the content moderation 
decisions they take and explain the reasons behind those decisions in so-called statements of reasons.

</div>

<div class="" style="width: 100% !important color: #404040 !important;
font: normal normal 400 1rem/1.5rem arial,sans-serif !important;">

To enhance transparency and facilitate scrutiny over content moderation decisions, **providers of 
online platforms need to submit these statements of reasons to the DSA Transparency Database**. The database 
allows to track the content moderation decisions taken by providers of online platforms in almost real-time. 
It also offers various tools for accessing, analysing, and downloading the information that platforms need to 
make available when they take content moderation decisions, contributing to the monitoring of the dissemination 
of illegal and harmful content online.

</div>


<div class="ecl-row">
<div class="ecl-col-l-4">

<x-ecl.card title="New to the Transparency Database?"
:links="[
[
'label' => 'General FAQ',
'url' => route('page.show', ['faq']) . '#general-faq'
],
[
'label' => 'Analytics',
'url' => route('analytics.index')
]
]"
/>

</div>
<div class="ecl-col-l-4">

<x-ecl.card title="Already a Data Expert?"
:links="[
[
'label' => 'Technical FAQ',
'url' => route('page.show', ['faq']) . '#technical-faq'
],
[
'label' => 'Data Download',
'url' => route('dayarchive.index')
],
[
'label' => 'Advanced Search',
'url' => route('statement.search')
]
]"
/>

</div>
<div class="ecl-col-l-4">

<x-ecl.card title="Platforms"
:links="[
[
'label' => 'Login',
'url' => route('profile.start')
],
[
'label' => 'Platforms FAQ',
'url' => route('page.show', ['faq']) . '#platforms-faq'
],
[
'label' => 'Documentation',
'url' => route('page.show', ['documentation'])
]
]"
/>

</div>
</div>

    