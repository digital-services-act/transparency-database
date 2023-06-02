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

## Sandbox

We highly encourage all users of the API to first test and try out their API code using the sandbox version of 
the application. This is a copy of the application and it is reset and blanked out each week. If your code if
is working there, then the only difference to the production version will be the URL endpoint and 
the token used.

<a href="{{ env('SANDBOX_URL') }}" target="_blank">SANDBOX VERSION</a>

## Creating a Statement

To create a statement of reason using the API you will need to make a
```POST``` request to this endpoint.

<pre>
    {{route('api.v'.config('app.api_latest').'.statement.store')}}
</pre>

For this request you will need to provide authorization, accept, and content type 
headers of the request:

<pre>
    Authorization: Bearer <YOUR_TOKEN>
    Accept: application/json
    Content-Type: application/json
</pre>

The body of your request needs to be a json encoded payload with the information of the statement

Example JSON payload body:

```json
{
    "decision_taken": "DECISION_TERMINATION",
    "decision_ground": "INCOMPATIBLE_CONTENT",
    "category": "FRAUD",
    "illegal_content_legal_ground": "illegal content legal ground",
    "illegal_content_explanation": "illegal content explanation",
    "incompatible_content_ground": "incompatible content ground",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": false,
    "countries_list": [
        "PT",
        "ES",
        "DE"
    ],
    "start_date": "2022-12-01 17:52:24",
    "source": "SOURCE_VOLUNTARY",
    "source_explanation": "source explanation",
    "automated_detection": "No",
    "automated_decision": "No",
    "automated_takedown": "Yes",
    "url": "https://theurl.com"
}
```

### The Response

When the request has been sent and it is correct, a response of ```201``` ```Created``` will be
sent back.

You will also receive a payload with the statement as created in the database:

```json
{
    "decision_taken": "DECISION_TERMINATION",
    "decision_ground": "INCOMPATIBLE_CONTENT",
    "category": "FRAUD",
    "incompatible_content_ground": "incompatible content ground",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": false,
    "countries_list": [
        "PT",
        "ES",
        "DE"
    ],
    "start_date": "2022-12-01 17:52:24",
    "source": "SOURCE_VOLUNTARY",
    "source_explanation": "source explanation",
    "automated_detection": "No",
    "automated_decision": "No",
    "automated_takedown": "Yes",
    "url": "https://theurl.com"
    "uuid": "28cc4759-614d-496f-90d6-a2645af37ff3",
    "permalink": "{{$baseurl}}/statement/28cc4759-614d-496f-90d6-a2645af37ff3",
    "self": "{{$baseurl}}/api/v{{config('app.api_latest')}}/statement/28cc4759-614d-496f-90d6-a2645af37ff3"
}
```

<x-ecl.message type="info" icon="information" title="Important" message="Anytime you make a call to an API you should always validate that you did receive the proper status, '201 Created'." close="" />

## UUID

Every statement created in the database receives an UUID which identifies the statement uniquely.

This UUID is then used in the urls for retrieving and viewing the statement online.

These urls are present in the response after creating as the "uuid", "permalink" and "self" attributes.

## Statement Attributes

The attributes of the statement take on two main forms.

* free textual, limited to 500 characters.
* limited, the value provided needs to be one of the allowed options

When submitting statements please take care to not submit ANY personal data. On a
regular basis we will do checks on the database to ensure that no personal data has been
submitted. However, we kindly ask that you help us out by not submitting any.

### Decision Taken (decision_taken)

This is a required attribute and it tells us what sort of a decision was taken. 

The value provided must be one of the following:

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

The value provided must be one of the following:

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

This is required if INCOMPATIBLE_CONTENT was the decision_ground. 
It is the reference to contractual ground.

### Incompatible Content Explanation (incompatible_content_explanation)

This is a small optional text that explains why the content is 
considered as incompatible on that ground.

### Incompatible Content Illegal (incompatible_content_illegal)

This is boolean that states if the incompatible content is also illegal.

Allowed values are: true, false

### Category (category)

This is a required attribute and it tells us which category the statment belongs to.

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::SOR_CATEGORIES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Countries List (countries_list)

This is a required array of countries involved. Each value must be the 2 letter iso code 
for the country and the countries must be EU countries.

Allowed values are:

@php echo implode(', ', \App\Models\Statement::EUROPEAN_COUNTRY_CODES); @endphp

### Start Date (start_date)

This is the date and time that this decision took place. The date needs to take the form of:

```YYYY-MM-DD HH:MM:SS```

The ```HH:MM:SS``` is optional and may be omitted.

### End Date (end_date)

This is the date and time that this decision ends. Leave blank for indefinite. 

The date needs to take the form of:

```YYYY-MM-DD HH:MM:SS```

The ```HH:MM:SS``` is optional and may be omitted.

### Source (source)

This is a required field and tells us the facts and circumstances 
relied upon in taking the decision.

The value provided must be one of the following:

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

### Source Explanation (source_explanation)

This is a required textual field to describe the source of the statement of reason.

### Automated Detection (automated_detection)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision taken in respect of automatically detected means.

### Automated Decision (automated_decision)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision carried out automatically.

### Automated Take-Down (automated_takedown)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that take-down was performed using automated means.

### URL (url)
This is an required attribute.
This contains the URL to the data that has been moderated.

## Code Examples

Below are various examples how to make this API call in different programming languages.

### PHP (guzzle)

```php
<?php
$client = new Client();
$headers = [
  'Accept' => 'application/json',
  'Authorization' => 'Bearer <YOUR_TOKEN_HERE>',
  'Content-Type' => 'application/json'
];
$body = '{
  "decision_taken": "DECISION_TERMINATION",
  "decision_ground": "INCOMPATIBLE_CONTENT",
  "category": "FRAUD",
  "illegal_content_legal_ground": "illegal content legal ground",
  "illegal_content_explanation": "illegal content explanation",
  "incompatible_content_ground": "incompatible content ground",
  "incompatible_content_explanation": "incompatible content explanation",
  "countries_list": [
    "PT",
    "ES",
    "DE"
  ],
  "start_date": "2022-12-01 17:52:24",
  "source": "SOURCE_VOLUNTARY",
  "source_explanation": "source explanation",
  "automated_detection": "No",
  "automated_decision": "No",
  "automated_takedown": "Yes",
  "url": "https://theurl.com"

}';
$request = new Request('POST', '{{$baseurl}}/api/statement/create', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
```

### Curl

```shell
curl --location '{{$baseurl}}/api/statement/create' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <YOUR_TOKEN_HERE>' \
--header 'Content-Type: application/json' \
--data '{
    "decision_taken": "DECISION_TERMINATION",
    "decision_ground": "INCOMPATIBLE_CONTENT",
    "category": "FRAUD",
    "illegal_content_legal_ground": "illegal content legal ground",
    "illegal_content_explanation": "illegal content explanation",
    "incompatible_content_ground": "incompatible content ground",
    "incompatible_content_explanation": "incompatible content explanation",
    "countries_list": [
    "PT",
    "ES",
    "DE"
    ],
    "start_date": "2022-12-01 17:52:24",
    "source": "SOURCE_VOLUNTARY",
    "source_explanation": "source explanation",
    "automated_detection": "No",
    "automated_decision": "No",
    "automated_takedown": "Yes",
    "url": "https://theurl.com"
}'
```


### Python

```php
import http.client
import json

conn = http.client.HTTPSConnection("{{str_replace("https://", "", $baseurl)}}")
payload = json.dumps({
  "decision_taken": "DECISION_TERMINATION",
  "decision_ground": "INCOMPATIBLE_CONTENT",
  "category": "FRAUD",
  "illegal_content_legal_ground": "illegal content legal ground",
  "illegal_content_explanation": "illegal content explanation",
  "incompatible_content_ground": "incompatible content ground",
  "incompatible_content_explanation": "incompatible content explanation",
  "countries_list": [
    "PT",
    "ES",
    "DE"
  ],
  "start_date": "2022-12-01 17:52:24",
  "source": "SOURCE_VOLUNTARY",
  "source_explanation": "source explanation",
  "automated_detection": "No",
  "automated_decision": "No",
  "automated_takedown": "Yes",
  "url": "https://theurl.com"
})
headers = {
  'Accept': 'application/json',
  'Authorization': 'Bearer <YOUR_TOKEN_HERE>',
  'Content-Type': 'application/json'
}
conn.request("POST", "/api/statement/create", payload, headers)
res = conn.getresponse()
data = res.read()
print(data.decode("utf-8"))
```
