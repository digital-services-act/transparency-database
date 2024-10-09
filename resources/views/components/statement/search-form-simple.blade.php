<form method="get" class="ecl-search-form" role="search">

    <x-ecl.search name="s" id="s" label="Search" placeholder="enter your text search here" :value="request()->get('s', '')" />

</form>

