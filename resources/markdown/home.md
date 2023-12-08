# Welcome to the DSA Transparency Database!

The Digital Services Act (DSA), obliges providers of hosting services to inform their users of the content 
moderation decisions they take and explain the reasons behind those decisions in so-called statements of reasons.  
To enhance transparency and facilitate scrutiny over content moderation decisions, **providers of online platforms 
need to submit these statements of reasons to the DSA Transparency Database**. The database allows to track the 
content moderation decisions taken by providers of online platforms in almost real-time. It also offers various 
tools for accessing, analysing, and downloading the information that platforms need to make available when they 
take content moderation decisions, contributing to the monitoring of the dissemination of illegal and harmful 
content online.





<div class="ecl-row">
<div class="ecl-col-l-4">

<x-ecl.card title="New to the Transparency Database?" 
    :links="[
        [
            'label' => 'General FAQ',
            'url' => route('page.show', ['faq']) . '#general-faq'
        ],
        [
            'label' => 'Dashboard',
            'url' => route('dashboard')
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
    'url' => route('dashboard')
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

    