@php use App\Models\Statement; @endphp
@props([
    'options' => null,
    'similarity_results' => null,
    'open_advanced_filter' => false
])

@php
    $advancedFilters = \App\Http\Requests\StatementSearchRequest::ADVANCED_FILTERS;

    $hasAdvancedErrors = collect($advancedFilters)->contains(fn ($field) =>
        $errors->has($field) || $errors->has($field.'.*')
    );

    $hasAdvancedInput = collect($advancedFilters)->contains(fn ($field) =>
        !empty(array_filter((array) request()->input($field)))
    );

    $shouldOpenAdvancedFilter = $hasAdvancedErrors || $hasAdvancedInput;
@endphp

<form method="get" action="{{ route('statement.index') }}">
    <div id="search-content">

        <div class="ecl-row">
            <div class="ecl-col-m-6">

                <x-ecl.textfield name="s" id="s" label="Search in the free-text fields" justlabel="true"
                    placeholder="enter your text search here" :value="request()->get('s', '')" />

                @if ($similarity_results)
                    <div class="ecl-u-mb-l" style="width: 400px;">
                        <span class="ecl-u-type-paragraph">
                            <strong>Similar Searches</strong>
                        </span>
                        <br />
                        @foreach ($similarity_results as $result)
                            <span class="ecl-u-type-paragraph-xs">
                                <a href="?s={{ $result }}" class="ecl-link">{{ $result }}</a>
                            </span>
                        @endforeach
                    </div>
                @endif

            </div>
            <div class="ecl-col-m-6">
                <x-ecl.select-multiple
                    label="Platform"
                    name="platform_id"
                    id="platform_id"
                    justlabel="true"
                    :options="$options['platforms']"
                    :default="request()->get('platform_id', [])"
                    :invalid="$errors->has('platform_id.*')"
                    :error="$errors->first('platform_id.*')"
                    select_all="All platforms"
                    select_item="Select one or more platforms" enter_keyword="Enter a platform name" />
            </div>

            <div class="ecl-col-m-6">
                <!-- @todo -->
                <x-ecl.select-multiple :label="Statement::LABEL_STATEMENT_DECISION_GROUND"
                    name="decision_ground"
                    id="decision_ground"
                    justlabel="true"
                    :default="request()->get('decision_ground', [])"
                    :options="$options['decision_grounds']"
                    select_all="All decisions grounds" select_item="Select one or more decision grounds"
                    :invalid="$errors->has('decision_ground.*')"
                    :error="$errors->first('decision_ground.*')"
                    enter_keyword="Enter a decision ground" />
            </div>

            <div class="ecl-col-m-6">

                <x-ecl.select-multiple
                    :label="Statement::LABEL_STATEMENT_SOURCE_TYPE"
                    name="source_type"
                    id="source_type"
                    justlabel="true"
                    :options="$options['source_types']"
                    :invalid="$errors->has('source_type.*')"
                    :error="$errors->first('source_type.*')"
                    select_all="All information sources" :default="request()->get('source_type', [])"
                    select_item="Select one or more information sources" enter_keyword="Enter an information source" />

            </div>
            <div class="ecl-col-m-6">

                <x-ecl.select-multiple
                    :label="Statement::LABEL_STATEMENT_CATEGORY"
                    name="category"
                    id="category"
                    justlabel="true"
                    :invalid="$errors->has('category.*')"
                    :error="$errors->first('category.*')"
                    :options="$options['categories']" select_all="All categories" :default="request()->get('category', [])"
                    select_item="Select one or more categories" enter_keyword="Enter a category" />

            </div>

            <div class="ecl-col-m-6">

                <x-ecl.select-multiple
                    :label="Statement::LABEL_STATEMENT_DECISION_VISIBILITY"
                    name="decision_visibility"
                    id="decision_visibility"
                    justlabel="true"
                    :invalid="$errors->has('decision_visibility.*')"
                    :error="$errors->first('decision_visibility.*')"
                    :options="$options['decision_visibilities']"
                    select_all="All visibility restrictions"
                    select_item="Select one or more visibility restrictions"
                    :default="request()->get('decision_visibility', [])"
                    enter_keyword="Enter a visibility restriction"
                />

            </div>
            <div class="ecl-col-m-6">

                <x-ecl.select-multiple
                    :label="Statement::LABEL_STATEMENT_DECISION_MONETARY"
                    name="decision_monetary"
                    id="decision_monetary"
                    justlabel="true"
                    :invalid="$errors->has('decision_monetary.*')"
                    :error="$errors->first('decision_monetary.*')"
                    :options="$options['decision_monetaries']"
                    select_all="All monetary restrictions"
                    select_item="Select one or more monetary restrictions"
                    :default="request()->get('decision_monetary', [])"
                    enter_keyword="Enter a monetary restriction"
                />

            </div>

            <div class="ecl-col-m-6">

                <x-ecl.select-multiple :label="Statement::LABEL_STATEMENT_DECISION_PROVISION"
                    name="decision_provision"
                    id="decision_provision"
                    justlabel="true"
                    :invalid="$errors->has('decision_provision.*')"
                    :error="$errors->first('decision_provision.*')"
                    :options="$options['decision_provisions']"
                    select_all="All service provision restrictions"
                    select_item="Select one or more service provision restrictions"
                    :default="request()->get('decision_provision', [])"
                    enter_keyword="Enter a service provision restriction"
                />

            </div>
            <div class="ecl-col-m-6">

                <x-ecl.select-multiple :label="Statement::LABEL_STATEMENT_DECISION_ACCOUNT"
                    name="decision_account"
                    id="decision_account"
                    justlabel="true"
                    :invalid="$errors->has('decision_account.*')"
                    :error="$errors->first('decision_account.*')"
                    :options="$options['decision_accounts']"
                    select_all="All account restrictions"
                    select_item="Select one or more account restrictions"
                    :default="request()->get('decision_account', [])"
                    enter_keyword="Enter an account restriction"
                />

            </div>
        </div>

        <x-ecl.accordion label="Advanced Filter" :open="$shouldOpenAdvancedFilter">

            <x-ecl.select-multiple
                :label="Statement::LABEL_STATEMENT_ACCOUNT_TYPE"
                name="account_type"
                id="account_type"
                justlabel="true"
                :invalid="$errors->has('account_type.*')"
                :error="$errors->first('account_type.*')"
                :options="$options['account_types']"
                select_all="All account types"
                select_item="Select one or more account types"
                enter_keyword="Enter an account type"
                :default="request()->get('account_type', [])"
            />

            <x-ecl.select-multiple
                :label="Statement::LABEL_KEYWORDS"
                name="category_specification"
                id="category_specification"
                justlabel="true"
                :invalid="$errors->has('category_specification.*')"
                :error="$errors->first('category_specification.*')"
                :options="$options['category_specifications']"
                select_all="All keywords"
                select_item="Select one or more keywords"
                enter_keyword="Enter a keyword"
                :default="request()->get('category_specification', [])"
            />

            <x-ecl.checkboxes-flex
                :label="Statement::LABEL_STATEMENT_TERRITORIAL_SCOPE"
                name="territorial_scope"
                id="territorial_scope"
                justlabel="true"
                :options="$options['countries']"
                :default="request()->get('territorial_scope', [])"
            />

            <p class="ecl-u-type-paragraph">
                Select:
                <a href="" class="ecl-link" id="select-eea-link">EEA</a> |
                <a href="" class="ecl-link" id="select-eu-link">EU</a> |
                <a href="" class="ecl-link" id="select-none-link">none</a>
            </p>

            <script>
                const eu_countries = {!! json_encode($options['eu_countries']) !!};
                const eea_countries = {!! json_encode($options['eea_countries']) !!};

                function clearCountries() {
                    let input = document.getElementsByName("territorial_scope[]");
                    for (let i = 0; i < input.length; i++) {
                        input[i].checked = false;
                    }
                }

                function checkCountries(countries) {
                    let input = document.getElementsByName("territorial_scope[]");
                    for (var i = 0; i < input.length; i++) {
                        if (countries.indexOf(input[i].value) > -1) {
                            input[i].checked = true;
                        }
                    }
                }

                document.addEventListener('DOMContentLoaded', (event) => {

                    ge('select-none-link').addEventListener('click', function(e) {
                        clearCountries();
                        e.preventDefault();
                        return false;
                    });

                    ge('select-eu-link').addEventListener('click', function(e) {
                        clearCountries();
                        checkCountries(eu_countries);
                        e.preventDefault();
                        return false;
                    });

                    ge('select-eea-link').addEventListener('click', function(e) {
                        clearCountries();
                        checkCountries(eea_countries);
                        e.preventDefault();
                        return false;
                    });

                });

                function ge(id) {
                    return document.getElementById(id);
                }
            </script>

            <x-ecl.select-multiple
                :label="Statement::LABEL_STATEMENT_CONTENT_TYPE"
                name="content_type"
                id="content_type"
                justlabel="true"
                :options="$options['content_types']"
                select_all="All content types"
                select_item="Select one or more content types"
                enter_keyword="Enter a content type"
                :default="request()->get('content_type', [])"
                :invalid="$errors->has('content_type.*')"
                :error="$errors->first('content_type.*')"
            />


            <x-ecl.select-multiple
                :label="Statement::LABEL_STATEMENT_CONTENT_LANGUAGE"
                name="content_language"
                justlabel="true"
                id="content_language"
                :default="request()->get('content_language', [])"
                :options="$options['languages']"
                select_all="All languages"
                select_item="Select one or more languages"
                enter_keyword="Enter a language name"
                :invalid="$errors->has('content_language.*')"
                :error="$errors->first('content_language.*')"
            />


            <x-ecl.checkboxes-flex
                :label="Statement::LABEL_STATEMENT_AUTOMATED_DETECTION"
                justlabel="true"
                name="automated_detection"
                id="automated_detection"
                :options="$options['automated_detections']"
                :default="request()->get('automated_detection', [])"
            />

            <x-ecl.checkboxes-flex
                :label="Statement::LABEL_STATEMENT_AUTOMATED_DECISION"
                justlabel="true"
                name="automated_decision"
                id="automated_decision"
                :options="$options['automated_decisions']"
                :default="request()->get('automated_decision', [])"
            />

            <x-ecl.datepicker
                label="Created Starting"
                id="created_at_start"
                justlabel="true"
                name="created_at_start"
                :value="request()->get('created_at_start', '')"
                disableFuture="true"
                :invalid="$errors->has('created_at_start')"
            />

            <x-ecl.datepicker
                label="Created Ending"
                id="created_at_end"
                justlabel="true"
                name="created_at_end"
                :value="request()->get('created_at_end', '')"
                disableFuture="true"
                :invalid="$errors->has('created_at_end')"
            />

        </x-ecl.accordion>

        <div class="ecl-u-mt-l">
            <x-ecl.cta-button label="Reset filters" url="/statement" priority="secondary" :icon="false" />
            <x-ecl.button label="Show results" />
        </div>

    </div>
</form>
