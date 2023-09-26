<div class="ecl-fact-figures ecl-fact-figures--col-1">
    <div class="ecl-fact-figures__description">
        On this page you can find some summary statistics on the statements of
        reasons submitted by providers of online platforms to the Commission.

        This page is a beta version of an analytics interface that will be revised and updated in future releases of
        the database. To submit feedback on the content of this page and to propose additional features, please
        visit the <a href="{{route("feedback.index")}}">feedback page</a>.

        You can also extract information from the database in .csv format, using the search functionalities and
        download options on the “<a href="{{route('statement.index')}}">Search for statements of reasons</a>” page.
    </div>
</div>

<h1 class="ecl-u-type-heading-1">Analytics <span style="font-size: 14px">(Analytics are updated once a day at midnight. Last update occurred {{Illuminate\Support\Carbon::now('UTC')->diffInHours(Illuminate\Support\Carbon::now('UTC')->startOfDay())}} hours ago.)</span></h1>

<div class="ecl-u-flex ecl-u-mb-l">
    <a href="{{ route('analytics.index') }}" class="ecl-link ecl-link--standalone">Overview</a> |
    <a href="{{ route('analytics.platforms') }}" class="ecl-link ecl-link--standalone">Platforms</a> |
    <a href="{{ route('analytics.restrictions') }}" class="ecl-link ecl-link--standalone">Restrictions</a> |
    <a href="{{ route('analytics.categories') }}" class="ecl-link ecl-link--standalone">Categories</a> |
    <a href="{{ route('analytics.keywords') }}" class="ecl-link ecl-link--standalone">Keywords</a> |
    <a href="{{ route('analytics.grounds') }}" class="ecl-link ecl-link--standalone">Grounds</a>
</div>
