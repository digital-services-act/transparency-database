@props(['statement' => null, 'options' => null])

<x-ecl.select label="Decision Taken"
              name="decision_taken"
              id="decision_taken"
              :options="$options['decisions']"
              required="true" default="{{ $statement->decision_taken }}"
              size="xl" />
<hr>

<x-ecl.select label="Ground for Decision"
              name="decision_ground"
              id="decision_ground" default="{{ $statement->decision_ground }}"
              :options="$options['decision_grounds']"
              required="true" />

<x-ecl.textfield label="Legal ground relied on" name="illegal_content_legal_ground" id="illegal_content_legal_ground" required="true"/>

<x-ecl.textarea label="Explanation of why the content is considered to be illegal on that ground"
                name="illegal_content_explanation" id="illegal_content_explanation" required="true" />

<x-ecl.textfield label="Reference to contractual ground" name="incompatible_content_ground"
                 id="incompatible_content_ground" required="true" />

<x-ecl.textarea label="Explanation of why the content is considered as incompatible on that ground"
                name="incompatible_content_explanation" id="incompatible_content_explanation" required="true" />

<x-ecl.checkbox label="Is the content considered as illegal ?"
                name="incompatible_content_illegal" id="incompatible_content_illegal" required="false" />
<hr>

<x-ecl.select label="Category"
              name="category"
              id="category" default="{{ $statement->category }}"
              :options="$options['categories']"
              required="true" />

<hr>

<x-ecl.select-multiple label="Territorial scope of the decision " name="countries_list" id="countries_list"
                       :options="$options['countries']" :default="$statement->countries_list"
                       select_all="European Union" select_item="Select a member state"
                       enter_keyword="Enter a country name" required="false" />
<hr>

<x-ecl.datepicker label="Start date of the decision" id="start_date"
                  name="start_date" value="{{ $statement->start_date }}" required="true"/>

<x-ecl.datepicker label="End date of the decision - leave blank for indefinite" id="end_date"
                  name="end_date" value="{{ $statement->end_date }}" />
<hr>

<x-ecl.select label="Facts and circumstances relied on in taking the decision" name="source" id="source" :options="$options['sources']" required="true" />



<x-ecl.textarea label="Please give an explanation of the notice (only if strictly necessary, identity of the notifier)." name="source_explanation" id="source_explanation_article_16" required="true" />


<x-ecl.textarea label="Please give an explanation of own voluntary initiative:" name="source_explanation" id="source_explanation_own" required="true" />

<hr>

<x-ecl.radio label="Was the content detected using automated means?"
             name="automated_detection"
             id="automated_detection"
             :options="$options['automated_detections']"
             default="{{ $statement->automated_detection }}"
             required="true"
/>

<x-ecl.radio label="Was the decision taken using automated means?"
             name="automated_decision"
             id="automated_decision"
             :options="$options['automated_decisions']"
             default="{{ $statement->automated_decision }}"
             required="true"
/>

<x-ecl.radio label="Was the content taken down using automated means?"
             name="automated_takedown"
             id="automated_takedown"
             :options="$options['automated_takedowns']"
             default="{{ $statement->automated_takedown }}"
             required="true"
/>

<hr>


<x-ecl.textfield label="Infringing URL/Hyperlink" name="url" id="url" required="true"/>

<hr>

    <script type="text/javascript">

      let form = document.getElementById("create-statement-form");

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

          hide('div_incompatible_content_ground');
          hide('div_incompatible_content_explanation');
          hide('div_incompatible_content_illegal');
          hide('div_source_explanation_article_16');
          hide('div_source_explanation_own');
          // hide('div_redress_more');

          if (ge('decision_ground').value === 'ILLEGAL_CONTENT') {
              show('div_illegal_content_legal_ground');
              show('div_illegal_content_explanation');
          }

          if (ge('decision_ground').value === 'INCOMPATIBLE_CONTENT') {
              show('div_incompatible_content_ground');
              show('div_incompatible_content_explanation');
              show('div_incompatible_content_illegal');
          }

          if (ge('source').value === 'SOURCE_ARTICLE_16') {
              show('div_source_explanation_article_16');
          }

          if (ge('source').value === 'SOURCE_VOLUNTARY') {
              show('div_source_explanation_own');
          }

      }

      function ge(id)
      {
        return document.getElementById(id);
      }

      function hide(id)
      {
        ge(id).classList.add('ecl-u-d-none');
      }

      function show(id)
      {
        ge(id).classList.remove('ecl-u-d-none');
      }

      initFields();

      document.getElementById('decision_ground').addEventListener('change', initFields);
      document.getElementById('source').addEventListener('change', initFields);

    </script>
