@props(['options' => null, 'similarity_results' => null])

<form method="get">

    <x-ecl.textfield name="s" id="s" label="Search and Filtering" justlabel="true" placeholder="enter your text search here" :value="request()->get('s', '')" />

    @if(request()->get('search'))
        <input type="hidden" name="search" value="{{ request()->get('search') }}" />
    @endif

    @if($similarity_results)
        <div class="ecl-u-mb-l" style="width: 400px;">
            <span class="ecl-u-type-paragraph">
                <strong>Similar Searches</strong>
            </span>
            <br />
            @foreach($similarity_results as $result)
                <span class="ecl-u-type-paragraph-xs">
                    <a href="?s={{ $result }}@if(request()->get('search'))&search={{ request()->get('search') }}@endif" class="ecl-link">{{ $result }}</a>
                </span>
            @endforeach
        </div>
    @endif

    <div class="ecl-u-f-r">
        @if(app('request')->input())<a class='ecl-u-type-paragraph ecl-link' href='{{ route(Route::current()->getName()) }}@if(request()->get('search'))?search={{ request()->get('search') }}@endif'>reset</a>@endif
        <x-ecl.button label="search" />
    </div>

    <div>
        <a href="{{ route('statement.search', request()->query()) }}" class="ecl-button ecl-button--secondary">Advanced Search</a>
    </div>

</form>