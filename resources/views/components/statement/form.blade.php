@php use App\Models\Statement; @endphp
@props(['statement' => null, 'options' => null])

<h4 class="ecl-u-type-heading-4">{{__('statement-create.Select visibility decision(s)')}}</h4>

<x-ecl.select-multiple :label="Statement::LABEL_STATEMENT_DECISION_VISIBILITY"
              name="decision_visibility"
              id="decision_visibility"
              :options="$options['decision_visibilities']"
              select_all="All"
              select_item="{{__('statement-create.Select visibility decision(s)')}}"
              enter_keyword="Enter a visibility decision"
              info_text="Additional Information AAA"
/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_FORM_OTHER" name="decision_visibility_other"
                 id="decision_visibility_other" required="true"/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_END_DATE_VISIBILITY_RESTRICTION"
                 name="end_date_visibility_restriction"
                 id="end_date_visibility_restriction"
                 value="{{ $statement->end_date_visibility_restriction }}"
                 placeholder="YYYY-MM-DD"
                 help="leading zeroes are required"
/>


<x-ecl.select :label="Statement::LABEL_STATEMENT_DECISION_MONETARY"
              name="decision_monetary"
              id="decision_monetary"
              :options="$options['decision_monetaries']"
              default="{{ $statement->decision_monetary }}"
              justlabel="true"
              :allow_null="true"
/>




<x-ecl.textfield :label="Statement::LABEL_STATEMENT_FORM_OTHER" name="decision_monetary_other"
                 id="decision_monetary_other" required="true"/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_END_DATE_MONETARY_RESTRICTION"
                 name="end_date_monetary_restriction"
                 id="end_date_monetary_restriction"
                 value="{{ $statement->end_date_monetary_restriction }}"
                 placeholder="YYYY-MM-DD"
                 help="leading zeroes are required"
/>


<x-ecl.select :label="Statement::LABEL_STATEMENT_DECISION_PROVISION"
              name="decision_provision"
              id="decision_provision"
              :options="$options['decision_provisions']"
              default="{{ $statement->decision_provision }}"
              justlabel="true"
              :allow_null="true"
/>



<x-ecl.textfield :label="Statement::LABEL_STATEMENT_END_DATE_SERVICE_RESTRICTION"
                 name="end_date_service_restriction"
                 id="end_date_service_restriction"
                 value="{{ $statement->end_date_service_restriction }}"
                 placeholder="YYYY-MM-DD"
                 help="leading zeroes are required"
/>


<x-ecl.select :label="Statement::LABEL_STATEMENT_DECISION_ACCOUNT"
                  name="decision_account"
                  id="decision_account"
                  :options="$options['decision_accounts']"
                  default="{{ $statement->decision_account }}"
                  justlabel="true"
                  :allow_null="true"
/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_END_DATE_ACCOUNT_RESTRICTION"
                 name="end_date_account_restriction"
                 id="end_date_account_restriction"
                 value="{{ $statement->end_date_account_restriction }}"
                 placeholder="YYYY-MM-DD"
                 help="leading zeroes are required"
/>



<hr>

<x-ecl.select :label="Statement::LABEL_STATEMENT_ACCOUNT_TYPE"
              name="account_type"
              id="account_type"
              :options="$options['account_types']"
              default="{{ $statement->account_type }}"
              justlabel="true"
              :allow_null="true"
/>

<hr>

<x-ecl.textarea :label="Statement::LABEL_STATEMENT_DECISION_FACTS" name="decision_facts" id="decision_facts"
                required="true"/>

<hr>

<x-ecl.select :label="Statement::LABEL_STATEMENT_DECISION_GROUND"
              name="decision_ground"
              id="decision_ground" default="{{ $statement->decision_ground }}"
              :options="$options['decision_grounds']"
              required="true"/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_DECISION_GROUND_REFERENCE_URL" name="decision_ground_reference_url" id="decision_ground_reference_url"
                 hover-text="Some help about the decision_ground_reference_url"
/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_ILLEGAL_CONTENT_GROUND" name="illegal_content_legal_ground"
                 id="illegal_content_legal_ground" required="true"/>

<x-ecl.textarea :label="Statement::LABEL_STATEMENT_ILLEGAL_CONTENT_EXPLANATION"
                name="illegal_content_explanation" id="illegal_content_explanation" required="true"/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_GROUND"
                 id="incompatible_content_ground" name="incompatible_content_ground" required="true"/>

<x-ecl.textarea :label="Statement::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_EXPLANATION"
                name="incompatible_content_explanation" id="incompatible_content_explanation" required="true"/>

<x-ecl.radio :label="Statement::LABEL_STATEMENT_INCOMPATIBLE_CONTENT_ILLEGAL"
             name="incompatible_content_illegal"
             id="incompatible_content_illegal"
             :options="$options['incompatible_content_illegals']"
             default="{{ $statement->incompatible_content_illegal }}"
/>

<hr>

<x-ecl.select-multiple :label="Statement::LABEL_STATEMENT_CONTENT_TYPE"
                       name="content_type"
                       id="content_type"
                       :options="$options['content_types']"
                       select_all="All"
                       select_item="Select content type(s)"
                       required="true"
                       enter_keyword="Enter a content type"/>


<x-ecl.textfield :label="Statement::LABEL_STATEMENT_FORM_OTHER" name="content_type_other"
                 id="content_type_other" required="true"/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_CONTENT_DATE"
                 name="content_date"
                 id="content_date"
                 value="{{ $statement->content_date }}"
                 required="true"
                 placeholder="YYYY-MM-DD"
                 help="leading zeroes are required"
/>

<x-ecl.select :label="Statement::LABEL_STATEMENT_CONTENT_LANGUAGE"
              name="content_language"
              id="content_language" default="{{ $statement->content_language }}"
              :options="$options['languages_grouped']"
/>


<hr>

<x-ecl.select :label="Statement::LABEL_STATEMENT_CATEGORY"
              name="category"
              id="category" default="{{ $statement->category }}"
              :options="$options['categories']"
              required="true"/>


<x-ecl.select-multiple :label="Statement::LABEL_STATEMENT_CATEGORY_ADDITION"
                       name="category_addition"
                       id="category_addition"
                       :options="$options['categories_addition']"
                       select_all="All"
                       select_item="Select category/categories"
                       enter_keyword="Enter a category"/>

<x-ecl.select-multiple :label="Statement::LABEL_KEYWORDS"
                       name="category_specification"
                       id="category_specification"
                       :options="$options['category_specifications']"
                       select_item="Select keyword(s)"
                       enter_keyword="Enter a keyword"
/>

<x-ecl.textfield :label="Statement::LABEL_KEYWORDS_OTHER" name="category_specification_other"
                 id="category_specification_other"/>


<hr>

<x-ecl.checkboxes-flex :label="Statement::LABEL_STATEMENT_TERRITORIAL_SCOPE"
                       name="territorial_scope"
                       id="territorial_scope"
                       justlabel="true"
                       width="10%"
                       :options="$options['countries']" :default="old('territorial_scope', [])"
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

        ge('select-none-link').addEventListener('click', function (e) {
            clearCountries();
            e.preventDefault();
            return false;
        });

        ge('select-eu-link').addEventListener('click', function (e) {
            clearCountries();
            checkCountries(eu_countries);
            e.preventDefault();
            return false;
        });

        ge('select-eea-link').addEventListener('click', function (e) {
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

<hr>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_APPLICATION_DATE"
                 name="application_date"
                 id="application_date"
                 value="{{ $statement->application_date }}"
                 required="true"
                 placeholder="YYYY-MM-DD"
                 help="leading zeroes are required"
/>

<hr>

<x-ecl.select :label="Statement::LABEL_STATEMENT_SOURCE_TYPE" name="source_type" id="source_type"
              :options="$options['source_types']"
              required="true"/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_SOURCE_IDENTITY" name="source_identity"
                 id="source_identity"/>

<hr>

<x-ecl.radio :label="Statement::LABEL_STATEMENT_AUTOMATED_DETECTION"
             name="automated_detection"
             id="automated_detection"
             :options="$options['automated_detections']"
             default="{{ $statement->automated_detection }}"
             required="true"
/>

<x-ecl.radio :label="Statement::LABEL_STATEMENT_AUTOMATED_DECISION"
             name="automated_decision"
             id="automated_decision"
             :options="$options['automated_decisions']"
             default="{{ $statement->automated_decision }}"
             required="true"
/>

<hr>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_PUID" name="puid" id="puid" required="true"/>

<hr>

<script>

    let form = ge("create-statement-form");

    form.addEventListener('submit', function (event) {

        //   //Do not submit the form if we click on the Close button from the multiple selects in ECL
        // console.log(event);
        //   if (event.submitter.innerText === 'Close') {
        //       //event.preventDefault();
        //   }

    });

    function initFields() {

        hide('div_illegal_content_legal_ground');
        hide('div_illegal_content_explanation');
        if (ge('decision_ground').value === 'DECISION_GROUND_ILLEGAL_CONTENT') {
            show('div_illegal_content_legal_ground');
            show('div_illegal_content_explanation');
        }

        hide('div_incompatible_content_ground');
        hide('div_incompatible_content_explanation');
        hide('div_incompatible_content_illegal');
        if (ge('decision_ground').value === 'DECISION_GROUND_INCOMPATIBLE_CONTENT') {
            show('div_incompatible_content_ground');
            show('div_incompatible_content_explanation');
            show('div_incompatible_content_illegal');
        }

        hide('div_decision_visibility_other');
        var selected_visibility_decisions = getSelectValues(ge('decision_visibility'));
        if (selected_visibility_decisions.includes('DECISION_VISIBILITY_OTHER')) {
            show('div_decision_visibility_other');
        }

        hide('div_category_specification_other');
        var selected_category_specifications = getSelectValues(ge('category_specification'));
        if (selected_category_specifications.includes('KEYWORD_OTHER')) {
            show('div_category_specification_other');
        }


        hide('div_decision_monetary_other');
        if (ge('decision_monetary').value === 'DECISION_MONETARY_OTHER') {
            show('div_decision_monetary_other');
        }

        hide('div_content_type_other');
        var selected_values = getSelectValues(ge('content_type'));
        if (selected_values.includes('CONTENT_TYPE_OTHER')) {
            show('div_content_type_other');
        }

        hide('div_end_date_visibility_restriction');
        if (getSelectValues(ge('decision_visibility')).length > 0) {
            show('div_end_date_visibility_restriction')
        }

        hide('div_end_date_monetary_restriction');
        if (ge('decision_monetary').value) {
            show('div_end_date_monetary_restriction')
        }

        hide('div_end_date_service_restriction');
        if (ge('decision_provision').value) {
            show('div_end_date_service_restriction')
        }

        hide('div_end_date_account_restriction');
        if (ge('decision_account').value) {
            show('div_end_date_account_restriction')
        }


        hide('div_source_identity');
        if (ge('source_type').value && ge('source_type').value !== 'SOURCE_VOLUNTARY') {
            show('div_source_identity');
        }

    }

    function ge(id) {
        return document.getElementById(id);
    }

    function hide(id) {
        ge(id).classList.add('ecl-u-d-none');
    }

    function show(id) {
        ge(id).classList.remove('ecl-u-d-none');
    }

    initFields();

    ge('decision_ground').addEventListener('change', initFields);
    ge('decision_visibility').addEventListener('change', initFields);
    ge('decision_monetary').addEventListener('change', initFields);
    ge('content_type').addEventListener('change', initFields);
    ge('decision_visibility').addEventListener('change', initFields);
    ge('category_specification').addEventListener('change', initFields);
    ge('source_type').addEventListener('change', initFields);
    ge('decision_visibility').addEventListener('change', initFields);
    ge('decision_monetary').addEventListener('change', initFields);
    ge('decision_provision').addEventListener('change', initFields);
    ge('decision_account').addEventListener('change', initFields);

    // Return an array of the selected opion values
    // select is an HTML select element
    function getSelectValues(select) {
        var result = [];
        var options = select && select.options;
        var opt;

        for (var i = 0, iLen = options.length; i < iLen; i++) {
            opt = options[i];

            if (opt.selected) {
                result.push(opt.value || opt.text);
            }
        }
        return result;
    }




</script>
