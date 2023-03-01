@props(['statement' => null, 'options' => null])

<x-ecl.select label="Decision Taken"
              name="decision_taken"
              id="decision_taken"
              :options="$options['decisions']"
              required=true default="{{ $statement->language }}"
              size="xl"/>
<hr>
<x-ecl.select label="Ground for Decision"
              name="decision_ground"
              id="decision_ground"
              :options="$options['decision_grounds']"
              required=true/>

    <x-ecl.textfield label="Legal ground relied on"
                     name="illegal_content_legal_ground"
                     id="illegal_content_legal_ground"/>
    <x-ecl.textarea label="Explanation of why the content is considered to be illegal on that ground"
                    name="illegal_content_explanation" id="illegal_content_explanation"/>
    <x-ecl.textfield label="Reference to contractual ground" name="incompatible_content_ground"
                     id="incompatible_content_ground"/>
    <x-ecl.textarea label="Explanation of why the content is considered as incompatible on that ground"
                    name="incompatible_content_explanation" id="incompatible_content_explanation"/>
    <hr>

    <x-ecl.select-multiple label="Territorial scope of the decision " name="countries_list" id="countries_list"
                           :options="$options['countries']" :default="$statement->countries_list" required="true"
                           select_all="European Union" select_item="Select a member state"
                           enter_keyword="Enter a country name"/>
    <hr>

    <x-ecl.datepicker label="Duration of the decision - leave blank for indefinite" id="date_abolished"
                      name="date_abolished" value="{{ $statement->date_abolished }}"/>
    <hr>
    <x-ecl.select label="Facts and circumstances relied on in taking the decision" name="source" id="source"
                  :options="$options['sources']" required=true/>
        <x-ecl.textfield label="Only if strictly necessary, identity of the notifier" name="source_identity"
                         id="source_identity"/>
        <x-ecl.textfield label="Other" name="source_other" id="source_other" required=true/>
            <hr>
            <x-ecl.radio
                label="Was the decision taken in respect of content detected or identified using automated means "
                name="automated_detection" id="automated_detection" :options="$options['automated_detections']"
                default="{{ $statement->automated_detection }}"/>
            <hr>
            <x-ecl.select label="Information on possible redress available to the recipient of the decision taken"
                          name="redress"
                          id="redress" :options="$options['redresses']"/>

            <x-ecl.textarea label="More Info" name="redress_more" id="redress_more"
                            value="{{ $statement->redress_more }}"/>


            <script type="text/javascript">

                let form = document.getElementById("create-statement-form");

                form.addEventListener('submit', function (event) {

                    //Do not submit the form if we click on the Close button from the multiple selects in ECL
                    if (event.submitter.innerText === 'Close') {
                        event.preventDefault();
                    }

                });

                function initFields() {

                    document.getElementById('div_illegal_content_legal_ground').style.display = 'none';
                    document.getElementById('div_illegal_content_explanation').style.display = 'none';
                    document.getElementById('div_incompatible_content_ground').style.display = 'none';
                    document.getElementById('div_incompatible_content_explanation').style.display = 'none';

                    document.getElementById('div_source_identity').style.display = 'none';
                    document.getElementById('div_source_other').style.display = 'none';

                    document.getElementById('div_redress_more').style.display = 'none';

                    if (document.getElementById('decision_ground').value === 'ILLEGAL_CONTENT') {
                        document.getElementById('div_illegal_content_legal_ground').style.display = 'block';
                        document.getElementById('div_illegal_content_explanation').style.display = 'block';
                    }

                    if (document.getElementById('decision_ground').value === 'INCOMPATIBLE_CONTENT') {
                        document.getElementById('div_incompatible_content_ground').style.display = 'block';
                        document.getElementById('div_incompatible_content_explanation').style.display = 'block';
                    }

                    if (document.getElementById('source').value === 'SOURCE_ARTICLE_16') {
                        document.getElementById('div_source_identity').style.display = 'block';
                    }

                    if (document.getElementById('source').value === 'SOURCE_OTHER') {
                        document.getElementById('div_source_other').style.display = 'block';
                    }

                    if (document.getElementById('redress').value !== '') {
                        document.getElementById('div_redress_more').style.display = 'block';
                    }
                }


                initFields();

                document.getElementById('decision_ground').addEventListener('change', initFields);

                document.getElementById('source').addEventListener('change', initFields);

                document.getElementById('redress').addEventListener('change', initFields);
            </script>
