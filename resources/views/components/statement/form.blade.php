@php use App\Models\Statement; @endphp
@props(['statement' => null, 'options' => null])

<h4>Decision Taken (Please select at least one item from the available options)</h4>

<x-ecl.select :label="Statement::LABEL_STATEMENT_DECISION_VISIBILITY"
              name="decision_visibility"
              id="decision_visibility"
              :options="$options['decision_visibilities']"
              default="{{ $statement->decision_visibility }}"
              justlabel="true"
              :allow_null="true"
/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_FORM_OTHER" name="decision_visibility_other"
                 id="decision_visibility_other" required="true"/>

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

<x-ecl.select :label="Statement::LABEL_STATEMENT_DECISION_PROVISION"
              name="decision_provision"
              id="decision_provision"
              :options="$options['decision_provisions']"
              default="{{ $statement->decision_provision }}"
              justlabel="true"
              :allow_null="true"
/>

<x-ecl.select :label="Statement::LABEL_STATEMENT_DECISION_ACCOUNT"
              name="decision_account"
              id="decision_account"
              :options="$options['decision_accounts']"
              default="{{ $statement->decision_account }}"
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
             required="true"
/>

<hr>

<x-ecl.select :label="Statement::LABEL_STATEMENT_CONTENT_TYPE"
              name="content_type"
              id="content_type"
              :options="$options['content_types']"
              required="true"/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_FORM_OTHER" name="content_type_other"
                 id="content_type_other" required="true"/>

<hr>

<x-ecl.select :label="Statement::LABEL_STATEMENT_CATEGORY"
              name="category"
              id="category" default="{{ $statement->category }}"
              :options="$options['categories']"
              required="true"/>

<hr>

<x-ecl.select-multiple :label="Statement::LABEL_STATEMENT_TERRITORIAL_SCOPE" name="territorial_scope" id="territorial_scope"
                       :options="$options['countries']" :default="$statement->territorial_scope"
                       select_all="All" select_item="Select member state(s)"
                       enter_keyword="Enter a country name" />
<hr>

<x-ecl.datepicker :label="Statement::LABEL_STATEMENT_START_DATE"
                  name="start_date" value="{{ $statement->start_date }}" required="true"/>

<x-ecl.datepicker :label="Statement::LABEL_STATEMENT_END_DATE . ' - leave blank for indefinite'" id="end_date"
                  name="end_date" value="{{ $statement->end_date }}"/>
<hr>

<x-ecl.select :label="Statement::LABEL_STATEMENT_SOURCE_TYPE" name="source_type" id="source_type" :options="$options['source_types']"
              required="true"/>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_SOURCE" name="source"
                 id="source" required="true"/>

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

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_URL" name="url" id="url" required="true"/>

<hr>

<x-ecl.textfield :label="Statement::LABEL_STATEMENT_PUID" name="puid" id="puid" required="true"/>

<hr>

<script type="text/javascript">

  let form = ge("create-statement-form");

  form.addEventListener('submit', function (event) {

    //   //Do not submit the form if we click on the Close button from the multiple selects in ECL
    // console.log(event);
    //   if (event.submitter.innerText === 'Close') {
    //       //event.preventDefault();
    //   }

  });

  function initFields () {

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
    if (ge('decision_visibility').value === 'DECISION_VISIBILITY_OTHER') {
      show('div_decision_visibility_other');
    }

    hide('div_decision_monetary_other');
    if (ge('decision_monetary').value === 'DECISION_MONETARY_OTHER') {
      show('div_decision_monetary_other');
    }

    hide('div_content_type_other');
    if (ge('content_type').value === 'CONTENT_TYPE_OTHER') {
      show('div_content_type_other');
    }

    hide('div_source');
    if (ge('source_type').value && ge('source_type').value != 'SOURCE_VOLUNTARY') {
      show('div_source');
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
  ge('source_type').addEventListener('change', initFields);

</script>
