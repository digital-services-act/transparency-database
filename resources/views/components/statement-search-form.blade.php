@props(['options' => null, 'similarity_results' => null])

<form method="get" action="{{ route('statement.index') }}">



    <x-ecl.textfield name="s" id="s" label="Search and Filtering" justlabel="true"
                     placeholder="enter your text search here" :value="request()->get('s', '')"/>

    @if($similarity_results)
        <div class="ecl-u-mb-l" style="width: 400px;">
            <span class="ecl-u-type-paragraph">
                <strong>Similar Searches</strong>
            </span>
            <br/>
            @foreach($similarity_results as $result)
                <span class="ecl-u-type-paragraph-xs">
                    <a href="?s={{ $result }}" class="ecl-link">{{ $result }}</a>
                </span>
            @endforeach
        </div>
    @endif





    <div id="search-content">


        <x-ecl.select-multiple label="Platform" name="platform_id" id="platform_id"
                               justlabel="true"
                               :options="$options['platforms']" :default="request()->get('platform_id', [])"
                               select_all="All platforms" select_item="Select one or more platforms"
                               enter_keyword="Enter a platform name" />

        <x-ecl.checkboxes
                label="Ground for Decision"
                justlabel="true"
                name="decision_ground"
                id="decision_ground"
                :default="request()->get('decision_ground', [])"
                :options="$options['decision_grounds']"
        />

        <x-ecl.checkboxes
                label="Decision Visibility"
                justlabel="true"
                name="decision_ground"
                id="decision_ground"
                :default="request()->get('decision_ground', [])"
                :options="$options['decision_grounds']"
        />

        <x-ecl.checkboxes
                label="Categories"
                justlabel="true"
                name="categories"
                id="categories"
                :default="request()->get('categories', [])"
                :options="$options['categories']"
        />

        <x-ecl.checkboxes
                label="Platform Type"
                justlabel="true"
                name="platform_type"
                id="platform_type"
                :default="request()->get('platform_type', [])"
                :options="$options['platform_types']"
        />

        <x-ecl.select-multiple label="Territorial scope of the decision " name="countries_list" id="countries_list"
                               justlabel="true"
                               :options="$options['countries']" :default="request()->get('countries_list', [])"
                               select_all="European Union" select_item="Select a member state"
                               enter_keyword="Enter a country name"/>

        <x-ecl.checkboxes
                label="Content Type"
                justlabel="true"
                name="content_type"
                id="content_type"
                :default="request()->get('content_type', [])"
                :options="$options['content_types']"
        />

        <x-ecl.checkboxes
                label="Automated Detection"
                justlabel="true"
                name="automated_detection"
                id="automated_detection"
                :options="$options['automated_detections']"
                :default="request()->get('automated_detection', [])"
        />

        <x-ecl.checkboxes
                label="Automated Decision"
                justlabel="true"
                name="automated_decision"
                id="automated_decision"
                :options="$options['automated_decisions']"
                :default="request()->get('automated_decision', [])"
        />

{{--        <x-ecl.checkboxes--}}
{{--                label="Automated Take-down"--}}
{{--                justlabel="true"--}}
{{--                name="automated_takedown"--}}
{{--                id="automated_takedown"--}}
{{--                :options="$options['automated_takedowns']"--}}
{{--                :default="request()->get('automated_takedown', [])"--}}
{{--        />--}}

        <x-ecl.checkboxes
                label="Source"
                justlabel="true"
                name="source"
                id="source"
                :options="$options['sources']"
                :default="request()->get('source', [])"
        />

        <x-ecl.datepicker label="Created Start" id="created_at_start" justlabel="true"
                          name="created_at_start" :value="request()->get('created_at_start', '')"/>

        <x-ecl.datepicker label="Created End" id="created_at_end" justlabel="true"
                          name="created_at_end" :value="request()->get('created_at_end', '')"/>

        <x-ecl.button label="search"/>

    </div>
</form>
