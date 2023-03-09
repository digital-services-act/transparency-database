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
    {{route('api.v'.config('app.api_latest').'.statement.store')}}
</pre>

For this request you will need to provide authorization, accept, and content type 
headers of the request:

<pre>
    Authorization: Bearer YOUR_TOKEN
    Accept: application/json
    Content-Type: application/json
</pre>

The body of your request needs to be a json encoded payload with the information of the statement

Example JSON payload body:

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
    "redress_more": "redress_more"
}
```

### The Response

When the request has been and it is correct, a response of ```201``` ```Created``` will be
sent back.

You will also receive a payload with the statement as created in the database:

```json
{
    "decision_taken": "DECISION_TERMINATION",
    "decision_ground": "INCOMPATIBLE_CONTENT",
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
    "automated_detection": "No",
    "redress": "REDRESS_INTERNAL_MECHANISM",
    "redress_more": "redress_more"
    "uuid": "28cc4759-614d-496f-90d6-a2645af37ff3"
}
```

<x-ecl.message type="info" icon="information" title="Important" message="Anytime you make a call to an API you should always validate that you did receive the proper status, '201 Created'." close="" />


## Statement Attributes Explained

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

### Incompatible_Content_Explanation (incompatible_content_explanation)

This is a small optional text that explains why the content is 
considered as incompatible on that ground.

### Countries List (countries_list)

This is a required array of countries involved. Each value must be the 2 letter iso code 
for the country and the countries must be EU countries.

Allowed values are:

@php echo implode(', ', \App\Models\Statement::EUROPEAN_COUNTRY_CODES); @endphp

### Date Abolished (date_abolished)

This is the date and time that this decision took place. The date needs to take the form of:

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

The value provided must be one of the following:

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
  "redress_more": "redress_more"
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
    "redress_more": "redress_more"
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
  "redress_more": "redress_more"
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


### Java
```javascript
OkHttpClient client = new OkHttpClient().newBuilder().build();
MediaType mediaType = MediaType.parse("application/json");
RequestBody body = RequestBody.create(mediaType, "{\n    \"decision_taken\": \"DECISION_TERMINATION\",\n    \"decision_ground\": \"INCOMPATIBLE_CONTENT\",\n    \"illegal_content_legal_ground\": \"illegal content legal ground\",\n    \"illegal_content_explanation\": \"illegal content explanation\",\n    \"incompatible_content_ground\": \"incompatible content ground\",\n    \"incompatible_content_explanation\": \"incompatible content explanation\",\n    \"countries_list\": [\n        \"PT\",\n        \"ES\",\n        \"DE\"\n    ],\n    \"date_abolished\": \"2022-12-01 17:52:24\",\n    \"source\": \"SOURCE_VOLUNTARY\",\n    \"source_identity\": \"source identity\",\n    \"source_other\": \"source other\",\n    \"automated_detection\": \"No\",\n    \"redress\": \"REDRESS_INTERNAL_MECHANISM\",\n    \"redress_more\": \"redress_more\"\n}");
Request request = new Request.Builder()
  .url("{{$baseurl}}/api/statement/create")
  .method("POST", body)
  .addHeader("Accept", "application/json")
  .addHeader("Authorization", "Bearer <YOUR_TOKEN_HERE>")
  .addHeader("Content-Type", "application/json")
  .build();
Response response = client.newCall(request).execute();
```