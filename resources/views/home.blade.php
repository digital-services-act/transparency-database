@extends('layouts/ecl')

@section('title', 'Home')

@section('breadcrumbs')
    <x-ecl.breadcrumb label="Home"/>
@endsection

@section('content')

    <h1 class="ecl-page-header__title ecl-u-type-heading-1">Welcome to the DSA Transparency Database!</h1>

    <p class="ecl-u-type-paragraph">
        Regulation (EU) 2022/2065 on a Single Market for Digital Services and amending Directive 2000/31/EC (<strong>Digital
            Services Act</strong>), or the ‘DSA’, is a <strong>comprehensive set of new rules</strong> regulating the
        responsibilities of digital
        services that act as intermediaries within the EU to connect consumers with goods, services and content. These
        intermediary services include online platforms, such as <strong>online marketplaces</strong> or social media
        networks, that store
        and disseminate information to the public at the request of the recipients of the service. You can find more
        information about the DSA <a
            href="https://commission.europa.eu/strategy-and-policy/priorities-2019-2024/europe-fit-digital-age/digital-services-act-ensuring-safe-and-accountable-online-environment_en">on
            the dedicated Commission website</a>.
    </p>
    <p class="ecl-u-type-paragraph">
        Article 17 of the DSA obliges providers of hosting services to send clear and specific <strong>statements of
            reasons</strong> to
        any affected recipient when they remove or otherwise restrict availability of and access to information provided
        by the recipient. In other words, providers of hosting services need to inform their users of the content
        moderation decisions they take and explain the reasons behind those decisions.
    </p>

    <p class="ecl-u-type-paragraph">
        To ensure transparency and to enable scrutiny over the content moderation decisions of the providers of online
        platforms and to monitor the spread of illegal and harmful content online, the <strong>DSA Transparency Database</strong>
        collects the <strong>statements of reasons submitted by providers of online platforms</strong> to the Commission, in accordance
        with Article 24(5) of the DSA.
    </p>

    <p class="ecl-u-type-paragraph">
        The database, managed by the Directorate-General for Communications Networks, Content and Technology of the
        Commission is publicly accessible and machine-readable. Providers of online platforms submit statements of
        reasons without undue delay in an automated manner to allow close to real-time updates of the database. You can
        find more information about the structure of the database and the individual data fields <a href="{{route('page.show','documentation')}}">here</a>. The source code
        of the database is publicly <a target="_blank" href="https://github.com/digital-services-act/transparency-database">available on GitHub</a>.
    </p>

    <p class="ecl-u-type-paragraph">
        This website offers various ways to access and analyse the statements of reasons, including search
        functionalities and a possibility to download data. The website also contains some summary statistics and
        aggregate visualisations in a beta version of an analytics interface that will be revised and updated in future
        releases of the database.
    </p>

    <p class="ecl-u-type-paragraph">
        The database does not contain personal information. It is the legal obligation of the providers of online
        platforms to ensure that the information submitted by them does not contain personal data. You can find more
        legal information <a href="{{route('legal-information')}}">here</a>.
    </p>

    <p class="ecl-u-type-paragraph">
        If you have any further questions, please consult the <a href="{{route('page.show','faq')}}">FAQ</a> section. Your <a href="{{route('feedback.index')}}">feedback</a> is welcome.
    </p>

@endsection
