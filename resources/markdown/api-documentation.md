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
    "account_type": "ACCOUNT_TYPE_BUSINESS",
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "decision_ground_reference_url": "https://www.anurl.com",
    "content_type": ["CONTENT_TYPE_VIDEO","CONTENT_TYPE_AUDIO","CONTENT_TYPE_SYNTHETIC_MEDIA"],
    "category": "STATEMENT_CATEGORY_FRAUD",
    "illegal_content_legal_ground": "illegal content legal grounds",
    "illegal_content_explanation": "illegal content explanation",
    "incompatible_content_ground": "incompatible content grounds",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": "Yes",
    "territorial_scope": [
        "PT",
        "ES",
        "DE"
    ],
    "content_language": "EN",
    "content_date": "2023-08-08-05",
    "application_date": "2023-08-08-08",
    "decision_facts": "facts about the decision",
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "automated_detection": "No",
    "automated_decision": "No",
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
    "account_type": "ACCOUNT_TYPE_BUSINESS",
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "decision_ground_reference_url": "https://www.anurl.com",
    "incompatible_content_ground": "incompatible content grounds",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": "Yes",
    "content_type": ["CONTENT_TYPE_VIDEO","CONTENT_TYPE_AUDIO","CONTENT_TYPE_SYNTHETIC_MEDIA"],
    "category": "STATEMENT_CATEGORY_FRAUD",
    "territorial_scope": [
        "PT",
        "ES",
        "DE"
    ],
    "content_language": "EN",
    "content_date": "2023-08-08-05",
    "application_date": "2023-08-08-08",
    "decision_facts": "facts about the decision",
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "automated_detection": "No",
    "automated_decision": "No",
    "puid": "TK421",
    "uuid": "7d0d0f7c-3ba9-45ba-966a-ec621eb17225",
    "created_at": "2023-08-08 08:08:08",
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


### Account Type (account_type)

This is an attribute that tells us about the account's type.

This attribute is optional.

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::ACCOUNT_TYPES as $key => $value) {
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


### Decision Ground Reference Url (decision_ground_reference_url)

This is an url to the TOS or Law relied upon in taking the decision.

This is an optional attribute.

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
considered as incompatible on that ground.

Limited to 2000 characters.

### Incompatible Content Illegal (incompatible_content_illegal)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that not only was the content incompatible but also illegal.

### Content Type (content_type)

This is a required attribute, and it tells us what type of content is targeted by the statement 
of reason.

The value provided must be an array with at least one of the following:

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

### Additional Categories (category_addition)

This is an optional attribute, and it tells us which additional categories the statement belongs to.

The value provided must be an array with one of the following:

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

### Territorial Scope (territorial_scope)

This is the optional territorial scope of the restriction. Each value must be the 2 letter iso code
for the country and the countries must be (EU/EEA) countries. 

The value provided must be an array.

Allowed values are:

@php echo implode(', ', \App\Services\EuropeanCountriesService::EUROPEAN_COUNTRY_CODES); @endphp

For European Union (EU) use:

@php echo '["' . implode('", "', \App\Services\EuropeanCountriesService::EUROPEAN_UNION_COUNTRY_CODES) . '"]'; @endphp

For European Economic Area (EEA) use:

@php echo '["' . implode('", "', \App\Services\EuropeanCountriesService::EUROPEAN_ECONOMIC_AREA_COUNTRY_CODES) . '"]'; @endphp

### Content Language (content_language)

This is the language that the content was in.

This attribute is optional.

The value though must be one of the uppercase two letter [ISO 639-1](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) codes.

Ex, 

@php echo '"' . implode('", "', \App\Services\EuropeanLanguagesService::EUROPEAN_LANGUAGE_CODES) . '"'; @endphp

### Content Date (content_date)

This is the date and hour that this content was uploaded or posted. The date needs to take the form of:

```YYYY-MM-DD-HH```

The day, month, and hour have leading zeroes.

The date must be after 2020-01-01.

### Application Date (application_date)

This is the date and hour that this decision starts from. The date needs to take the form of:

```YYYY-MM-DD-HH```

The day, month, and hour have leading zeroes.

The date must be after 2020-01-01.

### End Date (end_date)

This is the date and time that this decision ends. Leave blank for indefinite.

The date needs to take the form of:

```YYYY-MM-DD-HH```

The day, month, and hour have leading zeroes.

The date must be after or equal to the application date.

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

This is an optional field to describe the source/notifier if needed.

Limited to 500 characters.

### Automated Detection (automated_detection)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision taken in respect of automatically detected means.

### Automated Decision (automated_decision)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision carried out automatically.

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

You made an API with a blank JSON payload or an invalid JSON payload.

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
        "application_date": [
            "The application date field is required."
        ],
        "decision_facts": [
            "The decision facts field is required."
        ],
        "source_type": [
            "The source type field is required."
        ],
        "automated_detection": [
            "The automated detection field is required."
        ],
        "automated_decision": [
            "The automated decision field is required."
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
 
## Source Code

The source code for this application can be viewed here:

[DSA Transparency Database Source - GitHub](https://github.com/digital-services-act/transparency-database)

Using the repository code you can even setup and run a local replica development testing area.

Within the github environment you are also more than welcome to give feedback, pull requests and 
reviews concerning the source code. 

## Postman & Code Examples

In the source repository for this application you will find 2 files to help you setup a 
postman playground that will allow you to make random statements of reason using the API.

Postman will then allow you to generate code samples in many software coding languages
such as Java, PHP, CURL, Python and many many more.

- [Postman Requests Collection](https://github.com/digital-services-act/transparency-database/blob/main/DSA%20module%202%20API.postman_collection.json)
- [Sample Postman Local Environment](https://github.com/digital-services-act/transparency-database/blob/main/Local%20DSA%20M2.postman_environment.json) *

_* - The token in the local environment is not valid_
