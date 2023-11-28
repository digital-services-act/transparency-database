# Welcome to the DSA Transparency Database!

Regulation (EU) 2022/2065 on a Single Market for Digital Services and amending Directive 2000/31/EC 
(Digital Services Act), or the ‘DSA’, is a comprehensive set of new rules regulating the
responsibilities of digital services that act as intermediaries within the EU to connect consumers with goods, services and content. These
intermediary services include online platforms, such as online marketplaces or social media
networks, that store and disseminate information to the public at the request of the recipients of the service. You can find more
information about the DSA 
[on the dedicated Commission website](https://commission.europa.eu/strategy-and-policy/priorities-2019-2024/europe-fit-digital-age/digital-services-act-ensuring-safe-and-accountable-online-environment_en).





<div class="ecl-row">
<div class="ecl-col-l-4">

<x-ecl.card title="New to the Transparency Database?" 
    :links="[
        [
            'label' => 'General FAQ',
            'url' => route('page.show', ['faq'])
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
    'url' => route('dashboard.page.show',['api-documentation'])
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
    'url' => route('page.show', ['faq'])
    ],
    [
    'label' => 'Documentation',
    'url' => route('page.show', ['documentation'])
    ]
    ]"
/>

</div>
</div>

    