# How to use the API

Specific users of this database are given the ability to create
statements of reasons using an API endpoint. This greatly increases
efficiency and allows for automation.

## Requesting API access

If you would like to have API access to create statements of reason, then please
send an email request with your platform information to the following:

<pre>
    CNECT-DIGITAL-SERVICES-TECH&#64;ec.europa.eu
</pre>

Once verified your account will then be link to your platform and you will have the 
ability to generate an API token.

If you have other inquiries to discuss then please email the following:

<pre>
    CNECT-DIGITAL-SERVICES&#64;ec.europa.eu
</pre>

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

<x-ecl.message type="warning" icon="warning" title="Security Warning" message="This token identifies 
calls to the API as you! Do not share this token with other entities. 
They will be able to impersonate and act as you! If you believe that someone is using your token
please generate a new token immediately to invalidate the old one." close="" />

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
    "decision_visibility": "DECISION_VISIBILITY_CONTENT_DISABLED",
    "decision_monetary": "DECISION_MONETARY_TERMINATION",
    "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
    "decision_account": "DECISION_ACCOUNT_SUSPENDED",
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "content_type": "CONTENT_TYPE_VIDEO",
    "category": "STATEMENT_CATEGORY_FRAUD",
    "illegal_content_legal_ground": "illegal content legal grounds",
    "illegal_content_explanation": "illegal content explanation",
    "incompatible_content_ground": "incompatible content grounds",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": "Yes",
    "countries_list": [
        "PT",
        "ES",
        "DE"
    ],
    "start_date": "2022-12-01 17:52:24",
    "decision_facts": "facts about the decision",
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "source": "foomen",
    "automated_detection": "No",
    "automated_decision": "No",
    "url": "https://theurl.com",
    "puid": "TK421"
}
```

### The Response

When the request has been sent and it is correct, a response of ```201``` ```Created``` will be
sent back.

You will also receive a payload with the statement as created in the database:

```json
{
    "decision_visibility": "DECISION_VISIBILITY_CONTENT_DISABLED",
    "decision_monetary": "DECISION_MONETARY_TERMINATION",
    "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
    "decision_account": "DECISION_ACCOUNT_SUSPENDED",
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "incompatible_content_ground": "incompatible content grounds",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": "Yes",
    "content_type": "CONTENT_TYPE_VIDEO",
    "category": "STATEMENT_CATEGORY_FRAUD",
    "countries_list": [
        "PT",
        "ES",
        "DE"
    ],
    "start_date": "2022-12-01 17:52:24",
    "decision_facts": "facts about the decision",
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "source": "foomen",
    "automated_detection": "No",
    "automated_decision": "No",
    "url": "https://theurl.com",
    "puid": "TK421",
    "uuid": "7d0d0f7c-3ba9-45ba-966a-ec621eb17225",
    "created_at": "2023-06-08T20:02:50.000000Z",
    "permalink": ".... statement/7d0d0f7c-3ba9-45ba-966a-ec621eb17225",
    "self": ".... api/v1/statement/7d0d0f7c-3ba9-45ba-966a-ec621eb17225"
}
```

<x-ecl.message type="info" icon="information" title="Important" message="Anytime you make a call 
to an API you should always validate that you did receive the proper status, '201 Created'. 
If you did not receive a 201 Created, then the statement was not made, it is not in the database 
and you will need to retry at a later time." close="" />

## UUID

Every statement created in the database receives an UUID which identifies the statement uniquely.

This UUID is then used in the urls for retrieving and viewing the statement online.

These urls are present in the response after creating as the "uuid", "permalink" and "self" attributes.

## Statement Attributes

The attributes of the statement take on two main forms.

* free textual (max character limits apply, see below)
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
    foreach (\App\Models\Statement::DECISION_VISIBILITIES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

###  Decision Visibility Other (decision_visibility_other)

This is required if DECISION_VISIBILITY_OTHER was the decision_visibility.

Limited to 500 characters.

### Monetary payments suspension, termination or other restriction (decision_monetary)

This is an attribute that gives information about the Monetary payments suspension, termination or other restriction

This attribute is mandatory only if the following fields are empty: decision_visibility, decision_provision and decision_account

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISION_MONETARIES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

###  Decision Monetary Other (decision_monetary_other)

This is required if DECISION_MONETARY_OTHER was the decision_monetary. 

Limited to 500 characters.

### Decision about the provisioning of the service (decision_provision)

This is an attribute that tells us about the suspension or termination of the provision of the service.

This attribute is mandatory only if the following fields are empty: decision_visibility, decision_monetary and decision_account

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::DECISION_PROVISIONS as $key => $value) {
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
    foreach (\App\Models\Statement::DECISION_ACCOUNTS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Facts and circumstances relied on in taking the decision (decision_facts)

This is a required textual field to describe the facts and circumstances relied on in 
taking the decision.

Limited to 5000 characters.

### Decision Grounds (decision_ground)

This is a required field and tells us the basis on which the decision was taken.

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

### Illegal Content Legal Grounds (illegal_content_legal_ground)

This is required if the DECISION_GROUND_ILLEGAL_CONTENT was the decision_ground.
It is the legal grounds relied on.

Limited to 500 characters.

### Illegal Content Explanation (illegal_content_explanation)

This is required if the DECISION_GROUND_ILLEGAL_CONTENT was the decision_ground.
This is a small text that explains why the content was illegal.

Limited to 2000 characters.

### Incompatible Content Grounds (incompatible_content_ground)

This is required if DECISION_GROUND_INCOMPATIBLE_CONTENT was the decision_ground.
It is the reference to contractual grounds.

Limited to 500 characters.

### Incompatible Content Explanation (incompatible_content_explanation)

This is required if DECISION_GROUND_INCOMPATIBLE_CONTENT was the decision_ground.
This is a small text that explains why the content is
considered as incompatible on that grounds.

Limited to 2000 characters.

### Incompatible Content Illegal (incompatible_content_illegal)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that not only was the content incompatible but also illegal.

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

### Content Type Other (content_type_other)

This is required if CONTENT_TYPE_OTHER was the content_type.
It is a content type that is not text, video or an image.

Limited to 500 characters.

### Category (category)

This is a required attribute, and it tells us which category the statement belongs to.

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::STATEMENT_CATEGORIES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Countries List (countries_list)

This is the required territorial scope of the restriction. Each value must be the 2 letter iso code
for the country and the countries must be EU countries.

Allowed values are:

@php echo implode(', ', \App\Models\Statement::EUROPEAN_COUNTRY_CODES); @endphp

### Start Date (start_date)

This is the date and time that this decision starts from. The date needs to take the form of:

```YYYY-MM-DD HH:MM:SS```

The ```HH:MM:SS``` is optional and may be omitted. The date must be after 2020-01-01.

### End Date (end_date)

This is the date and time that this decision ends. Leave blank for indefinite.

The date needs to take the form of:

```YYYY-MM-DD HH:MM:SS```

The ```HH:MM:SS``` is optional and may be omitted.

### Information source (source_type)

This is a required field and tells us the facts and circumstances
relied upon in taking the decision.

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::SOURCE_TYPES as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Source/Notifier (source)

This is a required field if the source type field was a notice.

Limited to 500 characters.

### Automated Detection (automated_detection)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision taken in respect of automatically detected means.

### Automated Decision (automated_decision)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision carried out automatically.

### URL (url)

This is a required attribute. This contains the URL/URI to the data that has been moderated.
In cases where there is no URL or it is non applicable please supply "N/A". Additionally take
care to redact any personal identifying information.

Limited to 500 characters.

### Platform Unique Identifier (puid)

This is a string that uniquely identifies this statement within the platform.
This attribute is required and it must be unique within your platform.

Limited to 500 characters.

## Errors

When a call to the API has been made AND there was an error in the call you may 
expect the following to occur:

- You will NOT receive a HTTP Status Code ```201 Created```.
- The statement of reason has NOT been created.
- You receive back a payload that has more information in it.

For Ex,

You made an API with a blank JSON payload

```javascript
{}
```

The HTTP Status code coming back will be ```422 Unproccessable Content```

The payload body will be a JSON object containing more information and the errors in the API call.

```javascript
{
    "message": "The decision visibility field is required when none of decision monetary / decision provision / decision account are present. (and 13 more errors)",
    "errors": {
        "decision_visibility": [
            "The decision visibility field is required when none of decision monetary / decision provision / decision account are present."
        ],
        "decision_monetary": [
            "The decision monetary field is required when none of decision visibility / decision provision / decision account are present."
        ],
        "decision_provision": [
            "The decision provision field is required when none of decision visibility / decision monetary / decision account are present."
        ],
        "decision_account": [
            "The decision account field is required when none of decision visibility / decision monetary / decision provision are present."
        ],
        "decision_ground": [
            "The decision ground field is required."
        ],
        "content_type": [
            "The content type field is required."
        ],
        "category": [
            "The category field is required."
        ],
        "start_date": [
            "The start date field is required."
        ],
        "decision_facts": [
            "The decision facts field is required."
        ],
        "source_type": [
            "The source type field is required."
        ],
        "source": [
            "The source field is required when source type is a notice submission."
        ],
        "automated_detection": [
            "The automated detection field is required."
        ],
        "automated_decision": [
            "The automated decision field is required."
        ],
        "url": [
            "The url field is required."
        ],
        "puid": [
            "The puid field is required."
        ]
    }
}
```

The error messages for the individual fields will vary depending on what was attempted.

Such as the following:

If you sent 
```
{
    ...
    "automated_decision":"maybe"
    ...
}
```

"Maybe" is not a valid value for automated_decision. (only "Yes" or "No")

```javascript
{
    "message": "The selected automated decision is invalid.",
    "errors": {
        "automated_decision": [
            "The selected automated decision is invalid."
        ]
    }
}
```

Another common error that may occur when calling the API is that the authorization token is not valid.

This will result in a HTTP status code of ```401 Unauthorized```

The API authorization token needs to be double checked or a new API authorization token needs to be
generated. See again the section above: [Your API Token](#your-api-token)

In addition to the common ```422``` and ```401``` errors, Any of the standard 4XX HTTP can be
encountered. 4XX statuses generally indicate that there is an issue with your request. Please try to 
troubleshoot and resolve the problem.

When there is an error of 5XX we are immediately notified and there is no need 
to report the issue.
 