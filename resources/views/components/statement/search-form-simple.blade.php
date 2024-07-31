<form method="get" class="ecl-search-form" role="search">

    <x-ecl.search name="s" id="s" label="{{__('search-form-simple.label')}}" placeholder="{{__('search-form-simple.placeholder')}}" :value="request()->get('s', '')" />

</form>

