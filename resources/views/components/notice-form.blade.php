<x-ecl.textfield label="Title" name="title" id="title" required=true value="{{ $notice->title }}"/>
<x-ecl.textarea label="Body" name="body" id="body" value="{{ $notice->body }}" />
<x-ecl.select label="Language" name="language" id="language" :options="$options['languages']" required=true default="{{ $notice->language }}" />

<x-ecl.datepicker label="Date Sent" id="date_sent" name="date_sent" value="{{ $notice->date_sent }}" />
<x-ecl.datepicker label="Date Enacted" id="date_enacted" name="date_enacted" value="{{ $notice->date_enacted }}" />
<x-ecl.datepicker label="Date Abolished" id="date_abolished" name="date_abolished" value="{{ $notice->date_abolished }}" />

<x-ecl.select-multiple label="Countries" name="countries_list[]" id="countries_list" :options="$options['countries']" :default="$notice->countries_list" />

<x-ecl.radio label="Source" name="source" id="source" :options="$options['sources']" default="{{ $notice->source }}" />
<x-ecl.radio label="Payment Status" name="payment_status" id="payment_status" :options="$options['payment_statuses']" default="{{ $notice->payment_status }}" />
<x-ecl.radio label="Restriction Type" name="restriction_type" id="restriction_type" :options="$options['restriction_types']" default="{{ $notice->restriction_type }}" />
<x-ecl.textfield label="Restriction Type Other" name="restriction_type_other" id="restriction_type_other" value="{{ $notice->restriction_type_other }}"/>

<x-ecl.radio label="Automated Detection" name="automated_detection" id="automated_detection" :options="$options['automated_detections']" default="{{ $notice->automated_detection }}" />
<x-ecl.textarea label="Automated Detection More" name="automated_detection_more" id="automated_detection_more" value="{{ $notice->automated_detection_more }}" />

<x-ecl.textfield label="Illegal Content Legal Ground" name="illegal_content_legal_ground" id="illegal_content_legal_ground" value="{{ $notice->illegal_content_legal_ground }}"/>
<x-ecl.textarea label="Illegal Content Explanation" name="illegal_content_explanation" id="illegal_content_explanation" value="{{ $notice->illegal_content_explanation }}" />

<x-ecl.textfield label="TOC Contractual Ground" name="toc_contractual_ground" id="toc_contractual_ground" value="{{ $notice->toc_contractual_ground }}"/>
<x-ecl.textarea label="TOC Explanation" name="toc_explanation" id="toc_explanation" value="{{ $notice->toc_explanation }}" />

<x-ecl.radio label="Redress" name="redress" id="redress" :options="$options['redresses']" default="{{ $notice->redress }}" />
<x-ecl.textarea label="Redress More" name="redress_more" id="redress_more" value="{{ $notice->redress_more }}" />