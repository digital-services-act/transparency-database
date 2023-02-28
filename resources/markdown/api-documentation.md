# How to use the API

Specific users of this database are given the ability to create
statements of reasons using an API endpoint. This greatly increases
efficiency and allows for automation.

## Your API token

When your account is given the ability to use the API then you are able to
generate a private secure token that will allow you to use the API.

This token looks something like this: 

<pre>
    X|ybqkCFX7ZkIFoLxtI0VAk1JBzMR9jVk4c4EU
</pre>

If you do not know your token or need to generate a new token you may do so
in the [dashboard](/dashboard) of this application. Simply click the button "Generate New Token"

This token will be shown one time, so it will need to be copied and stored safely.

__Each time you generate a new token the old token becomes invalid!__

<x-ecl.message type="warning" icon="warning" title="Security Warning" message="This token identifies calls to the API as you! Do not share this token with other entities. They will be able to impersonate and act as you!" close="" />

## Creating a Statement

To create a statement of reason using the API you will need to make a
```POST``` request to this endpoint.

<pre>
    {{$baseurl}}/api/statement
</pre>

For this request you will need to provide authorization and the accept in the headers of the request:

<pre>
    Authorization: Bearer YOUR_TOKEN
    Accept: application/json
</pre>

The body of your request needs to be a json encoded payload with the information of the statement

Example
```json
{
    "decision_taken": "DECISION_TERMINATION",
    "decision_ground": "INCOMPATIBLE_CONTENT",
    "illegal_content_legal_ground": "illegal content legal ground",
    "illegal_content_explanation": "illegal content explanation",
    "incompatible_content_ground": "incompatible content ground",
    "incompatible_content_explanation": "incompatible content explanation",
    "countries_list": [
        "PT",
        "ES",
        "DE"
    ],
    "date_abolished": "2022-12-01 17:52:24",
    "source": "SOURCE_VOLUNTARY",
    "source_identity": "source identity",
    "source_other": "source other",
    "automated_detection": "No",
    "redress": "REDRESS_INTERNAL_MECHANISM",
    "redress_more": "redress_more",
}
```

### The Response

When the request has been and it is correct, a response of ```OK```

## Statement Attributes Explained

In order to provide statistics and gain insights into the statements of reason we require that
certain attributes of a statement be limited to specific values. This means that we can then
later quantify and filter statements based on common shared values for certain attributes. Other
attributes of a statement are simply textual fields. Typically with a limit of 500 characters.

### Decision Taken (decision_taken)

This is a required attribute and it tells us what sort of a decision was taken.

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISIONS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>


### Decision Ground (decision_ground)

This is a required attribute and it tells us which ground the decision was based on.

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISION_GROUNDS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>


### Illegal Content Legal Ground (illegal_content_legal_ground)

This is required if the ILLEGAL_CONTENT was the decision_ground. It is the legal ground relied on.

### Illegal Content Explanation (illegal_content_explanation)

This is a small optional text that explains why the content was illegal.


### Incompatible Content Ground (incompatible_content_ground)

This is required if the INCOMPATIBLE_CONTENT was the decision_ground. It is the reference to contractual ground.

### Incompatible_Content_Explanation (incompatible_content_explanation)

This is a small optional text that explains why the content is considered as incompatible on that ground.

### Countries List (countries_list)

This is a required array of countries involved each value must be the 2 letter iso code 
for the country and the countries must be EU countries.

@php echo implode(', ', \App\Models\Statement::EUROPEAN_COUNTRY_CODES); @endphp

### Date Abolished (date_abolished)

This is the date and time that this decision took place. The date needs to take the form of:

```YYYY-MM-DD HH:MM:SS```

The ```HH:MM:SS``` is optional and may be omitted.

### Source (source)

This is a required field and tells us the facts and circumstances relied on in taking the decision.

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::SOURCES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Source Other (source_other)

This is a required textual field if the source above was SOURCE_OTHER.

### Source Identity (source_identity)

This is an optional textual field and it identifies the source of this statement.

### Automated Detection (automated_detection)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision taken in respect of 
content detected or identified using automated means.

### Redress (redress)

This is an optional field and tells us the possible redress available to the recipient of the decision taken.

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::REDRESSES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Redress More (redress_more)

This is an optional field and describes more information about the redress.

## Examples (PHP, cUrl, Python, Java)

