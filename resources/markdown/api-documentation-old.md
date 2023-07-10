# How to use the API

Specific users of this database are given the ability to create
statements of reasons using an API endpoint. This greatly increases
efficiency and allows for automation.

The also API allows for searching and statistics gathering. By making requests with 
search parameters and filters data can be gathered for reporting purposes.

## Your API Token

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

<x-ecl.message type="warning" icon="warning" title="Security Warning" message="This token identifies calls to the API as
you! Do not share this token with other entities. They will be able to impersonate and act as you!" close="" />

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
    "decision_visibility": "CONTENT_DISABLED",
    "decision_monetary": "MONETARY_TERMINATION",
    "decision_provision": "PARTIAL_TERMINATION",
    "decision_account": "ACCOUNT_TERMINATED",
    "decision_ground": "INCOMPATIBLE_CONTENT",
    "content_type": "VIDEO",
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
    "decision_facts": "facts about the decision",
    "source": "SOURCE_VOLUNTARY",
    "automated_detection": "No",
    "automated_decision": "No",
    "url": "https://theurl.com"
}
```

### The Response

When the request has been sent and it is correct, a response of ```201``` ```Created``` will be
sent back.

You will also receive a payload with the statement as created in the database:

```json
{
    "decision_visibility": "CONTENT_DISABLED",
    "decision_monetary": "MONETARY_TERMINATION",
    "decision_provision": "PARTIAL_TERMINATION",
    "decision_account": "ACCOUNT_TERMINATED",
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
    "decision_facts": "facts about the decision",
    "automated_detection": "No",
    "automated_decision": "No",
    "url": "https://theurl.com",
    "uuid": "28cc4759-614d-496f-90d6-a2645af37ff3",
    "permalink": "{{$baseurl}}/statement/28cc4759-614d-496f-90d6-a2645af37ff3",
    "self": "{{$baseurl}}/api/v{{config('app.api_latest')}}/statement/28cc4759-614d-496f-90d6-a2645af37ff3"
}
```

<x-ecl.message type="info" icon="information" title="Important" message="Anytime you make a call to an API you should
always validate that you did receive the proper status, '201 Created'." close="" />

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

### Decision Visibility (decision_visibility)

This attribute tells us the visibility restriction of specific items of information provided by the
recipient of the service.

This attribute is mandatory only if the following fields are empty: decision_monetary, decision_provision and decision_account

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISIONS_VISIBILITY as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Decision about the content visibility (decision_monetary)

This is an attribute that gives information about the Monetary payments suspension, termination or other restriction

This attribute is mandatory only if the following fields are empty: decision_visibility, decision_provision and decision_account

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISIONS_MONETARY as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Decision about the provisioning of the service (decision_provision)

This is an attribute that tells us about the suspension or termination of the provision of the service.

This attribute is mandatory only if the following fields are empty: decision_visibility, decision_monetary and decision_account

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISIONS_PROVISION as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Decision about the account's status (decision_account)

This is an attribute that tells us about the account's status.

This attribute is mandatory only if the following fields are empty: decision_visibility, decision_monetary and decision_provision

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISIONS_ACCOUNT as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Facts and circumstances relied on in taking the decision (decision_facts)

This is a required textual field to describe the facts and circumstances relied on in taking the decision.

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

### Content Type (content_type)

This is a required attribute, and it tells us what type of content is targeted by the statement of reason.

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::CONTENT_TYPES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Category (category)

This is a required attribute, and it tells us which category the statement belongs to.

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



### Information source (source)

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

### Automated Detection (automated_detection)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision taken in respect of automatically detected means.

### Automated Decision (automated_decision)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision carried out automatically.


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
  "decision_visibility": "CONTENT_DISABLED",
  "decision_monetary": "MONETARY_TERMINATION",
  "decision_provision": "PARTIAL_TERMINATION",
  "decision_account": "ACCOUNT_TERMINATED",
  "decision_ground": "INCOMPATIBLE_CONTENT",
  "content_type": "VIDEO",
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
  "decision_facts": "facts about the decision",
  "automated_detection": "No",
  "automated_decision": "No",
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
    "decision_visibility": "CONTENT_DISABLED",
    "decision_monetary": "MONETARY_TERMINATION",
    "decision_provision": "PARTIAL_TERMINATION",
    "decision_account": "ACCOUNT_TERMINATED",
    "decision_ground": "INCOMPATIBLE_CONTENT",
    "content_type": "VIDEO",
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
    "decision_facts": "facts about the decision",
    "automated_detection": "No",
    "automated_decision": "No",
    "url": "https://theurl.com"
}'
```

### Python

```php
import http.client
import json

conn = http.client.HTTPSConnection("{{str_replace("https://", "", $baseurl)}}")
payload = json.dumps({
  "decision_visibility": "CONTENT_DISABLED",
  "decision_monetary": "MONETARY_TERMINATION",
  "decision_provision": "PARTIAL_TERMINATION",
  "decision_account": "ACCOUNT_TERMINATED",
  "decision_ground": "INCOMPATIBLE_CONTENT",
  "content_type": "VIDEO",
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
  "decision_facts": "facts about the decision",
  "automated_detection": "No",
  "automated_decision": "No",
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


## Searching Statements

Users with a valid API token can also make search requests. The data returned in these 
search requests can be used for statistical purposes or for finding specific statements.

### Basic Query

Make a simple get ```GET``` request to this endpoint: 

```
{{ route('api.v1.statement.search') }}
```

This will return a set of paginated statements (50) starting with the most recent created first.

```javascript
{
    "current_page": 1,
    "data": [
        {
            "uuid": "bb5f698f-5b91-43b4-9750-e0017d0dc1a4",
            "decision_taken": "DECISION_PROVISION",
            "decision_ground": "ILLEGAL_CONTENT",
            "category": "CHILD_SAFETY",
            "content_type": "IMAGE",
            "illegal_content_legal_ground": "Culpa sed ea autem perferendis autem. Sed omnis non odio. Ut sit voluptatem et aut vel est.",
            "illegal_content_explanation": "White Rabbit blew three blasts on the Duchess's cook. She carried the pepper-box in her pocket, and was going to say,' said the Mock Turtle went on. 'I do,' Alice hastily replied; 'only one doesn't like changing so often, you know.' Alice had not attended to this last remark, 'it's a vegetable. It doesn't look like it?' he said, 'on and off, for days and days.' 'But what happens when one eats cake, but Alice had been anxiously looking across the field after it, 'Mouse dear! Do come back with.",
            "incompatible_content_ground": "Architecto ut aut quia id iure. Neque ab maxime labore placeat. Dignissimos rem sint ea non.",
            "incompatible_content_explanation": "Nobody moved. 'Who cares for you?' said the King hastily said, and went by without noticing her. Then followed the Knave of Hearts, she made some tarts, All on a three-legged stool in the middle, nursing a baby; the cook had disappeared. 'Never mind!' said the Duchess, digging her sharp little chin. 'I've a right to grow up again! Let me think: was I the same thing as \"I eat what I get\" is the capital of Paris, and Paris is the capital of Rome, and Rome--no, THAT'S all wrong, I'm certain! I.",
            "incompatible_content_illegal": 0,
            "countries_list": [
                "FR",
                "NL",
                "HR",
                "CZ",
                "BG",
                "FI",
                "RO"
            ],
            "start_date": "2023-06-05 01:25:44",
            "end_date": "2023-06-05 01:25:44",
            "decision_facts": "Queen, who was a most extraordinary noise going on shrinking rapidly: she soon made out the verses on his flappers, '--Mystery, ancient and modern, with Seaography: then Drawling--the Drawling-master was an immense length of neck, which seemed to be no doubt that it was YOUR table,' said Alice; 'that's not at all anxious to have changed since her swim in the pictures of him), while the rest were quite dry again, the cook and the executioner myself,' said the Mock Turtle. 'And how do you want.",
            "source": "SOURCE_VOLUNTARY",
            "automated_detection": "Yes",
            "automated_decision": "No",
            "automated_takedown": "No",
            "url": "http://muller.com/velit-quis-sint-voluptates-amet-quos.html",
            "created_at": "2023-06-05T01:25:44.000000Z",
            "permalink": "{{ route('home') }}/statement/bb5f698f-5b91-43b4-9750-e0017d0dc1a4",
            "self": "{{ route('home') }}/api/v1/statement/bb5f698f-5b91-43b4-9750-e0017d0dc1a4"
        },
        ...
        ...
        ...
        ...
    },
    "first_page_url": "{{ route('api.v1.statement.search') }}?page=1",
    "from": 1,
    "last_page": 20,
    "last_page_url": "{{ route('api.v1.statement.search') }}?page=20",
    "links": [ ... ],
    "next_page_url": "{{ route('api.v1.statement.search') }}?page=2",
    "path": "{{ route('api.v1.statement.search') }}",
    "per_page": 50,
    "prev_page_url": null,
    "to": 50,
    "total": 1000
    }
```

The interesting information here is the ```total``` attribute that is return with this call. 
This tells us how many statements were found with this search query. In this example case
it tells us how many total statements there are in the entire database. Our sample sandbox 
environment we create 1000 randomly generated statements.

From here we can then start to make more interesting searches using filters and parameters.

### Filters and Parameters

To begin filtering the results we make the same ```GET``` request to the same endpoint:

```
{{ route('api.v1.statement.search') }}
```

However, this time we are going to add a filter. We are going to ask for the statements
that were detected automatically. To do this we add to the request url an query parameter
array with the option values we want to filter on. 

ex,

```
{{ route('api.v1.statement.search') }}?automated_detection[]=Yes
```

In our example of 1000 statements of reason this would return a total of around +/- 500.

```javascript
{
    ...
    "total": 491
}
```

As a matter of exercise, if we were to add the filter values "Yes" and "No" to the array 
we would get 1000 again, 491 Yes, 509 No.

```
{{ route('api.v1.statement.search') }}?automated_detection[]=Yes&automated_detection[]=No
```

```javascript
{
    ...
    "total": 1000
}
```

### Available Filters

