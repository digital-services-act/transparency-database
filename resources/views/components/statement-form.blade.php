@props(['statement' => null, 'options' => null])
<x-ecl.textfield label="Title" name="title" id="title" required=true value="{{ $statement->title }}"/>
<x-ecl.textarea label="Body" name="body" id="body" value="{{ $statement->body }}" />
<x-ecl.select label="Language" name="language" id="language" :options="$options['languages']" required=true default="{{ $statement->language }}" />

<x-ecl.datepicker label="Date Sent" id="date_sent" name="date_sent" value="{{ $statement->date_sent }}" />
<x-ecl.datepicker label="Date Enacted" id="date_enacted" name="date_enacted" value="{{ $statement->date_enacted }}" />
<x-ecl.datepicker label="Date Abolished" id="date_abolished" name="date_abolished" value="{{ $statement->date_abolished }}" />

<x-ecl.select-multiple label="Countries" name="countries_list" id="countries_list" :options="$options['countries']" :default="$statement->countries_list" />

<x-ecl.radio label="Source" name="source" id="source" :options="$options['sources']" default="{{ $statement->source }}" />
<x-ecl.radio label="Payment Status" name="payment_status" id="payment_status" :options="$options['payment_statuses']" default="{{ $statement->payment_status }}" />
<x-ecl.radio label="Restriction Type" name="restriction_type" id="restriction_type" :options="$options['restriction_types']" default="{{ $statement->restriction_type }}" />
<x-ecl.textfield label="Restriction Type Other" name="restriction_type_other" id="restriction_type_other" value="{{ $statement->restriction_type_other }}"/>

<x-ecl.radio label="Automated Detection" name="automated_detection" id="automated_detection" :options="$options['automated_detections']" default="{{ $statement->automated_detection }}" />
<x-ecl.textarea label="Automated Detection More" name="automated_detection_more" id="automated_detection_more" value="{{ $statement->automated_detection_more }}" />

<x-ecl.textfield label="Illegal Content Legal Ground" name="illegal_content_legal_ground" id="illegal_content_legal_ground" value="{{ $statement->illegal_content_legal_ground }}"/>
<x-ecl.textarea label="Illegal Content Explanation" name="illegal_content_explanation" id="illegal_content_explanation" value="{{ $statement->illegal_content_explanation }}" />

<x-ecl.textfield label="TOC Contractual Ground" name="toc_contractual_ground" id="toc_contractual_ground" value="{{ $statement->toc_contractual_ground }}"/>
<x-ecl.textarea label="TOC Explanation" name="toc_explanation" id="toc_explanation" value="{{ $statement->toc_explanation }}" />

<x-ecl.radio label="Redress" name="redress" id="redress" :options="$options['redresses']" default="{{ $statement->redress }}" />
<x-ecl.textarea label="Redress More" name="redress_more" id="redress_more" value="{{ $statement->redress_more }}" />
