# How to use the API

Specific users of this database are given the ability to create
statements of reasons using an API endpoint. This greatly increases
efficiency and allows for automation.

## Requesting API access

To set up your statement of reasons submission process, please
register [here](https://ec.europa.eu/eusurvey/runner/DSA-ComplianceStamentsReasons) regarding your obligations under
article 24(5) of the DSA.
After receiving your registration form, the Digital Service Coordinator of your Member State will contact you providing
the details on how to complete the onboarding of your online platform.

Once you are onboarded via your Digital Service Coordinator, you will gain access to a sandbox environment to test your
submissions to the DSA Transparency Database, which you can perform either via an Application Programming Interface (
API) or a webform, according to the volume of your data and technical needs.

Once the testing phase is completed, you will be able to move to the production environment of the DSA Transparency
Database, where you can start submitting your statement of reasons via an API or a webform.

## Your API Token

When your account is given the ability to use the API then you are able to
generate a private secure token that will allow you to use the API.

This token looks something like this:

<pre>
    X|ybqkCFX7ZkIFoLxtI0VAk1JBzMR9jVk4c4EU
</pre>

If you do not know your token or need to generate a new token you may do so
in the [user profile](/profile/start) of this application. Simply click the button "Generate New Token"

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
    Authorization: Bearer YOUR_TOKEN
    Accept: application/json
    Content-Type: application/json
</pre>

The body of your request needs to be a json encoded payload with the information of the statement

Example JSON payload body:

```json
{
    "decision_visibility": [
        "DECISION_VISIBILITY_CONTENT_DISABLED"
    ],
    "decision_monetary": "DECISION_MONETARY_TERMINATION",
    "end_date_monetary_restriction": "2023-08-08",
    "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
    "decision_account": "DECISION_ACCOUNT_SUSPENDED",
    "account_type": "ACCOUNT_TYPE_BUSINESS",
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "decision_ground_reference_url": "https://www.anurl.com",
    "content_type": [
        "CONTENT_TYPE_VIDEO",
        "CONTENT_TYPE_AUDIO",
        "CONTENT_TYPE_SYNTHETIC_MEDIA"
    ],
    "category": "STATEMENT_CATEGORY_CYBER_VIOLENCE_AGAINST_WOMEN",
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
    "content_date": "2023-08-08",
    "content_id": {
        "EAN-13" : "0123456789123"
    },
    "application_date": "2023-08-08",
    "decision_facts": "facts about the decision",
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "automated_detection": "No",
    "automated_decision": "AUTOMATED_DECISION_PARTIALLY",
    "puid": "TK421"
}
```

### The Response

When the request has been sent and it is correct, a response of ```201``` ```Created``` will be
sent back.

You will also receive a payload with the statement as created in the database:

```json
{    
    "content_id": {
        "EAN-13": "0123456789123"
    },
    "decision_visibility": [
        "DECISION_VISIBILITY_CONTENT_DISABLED"
    ],
    "decision_monetary": "DECISION_MONETARY_TERMINATION",
    "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
    "decision_account": "DECISION_ACCOUNT_SUSPENDED",
    "account_type": "ACCOUNT_TYPE_BUSINESS",
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "decision_ground_reference_url": "https://www.anurl.com",
    "incompatible_content_ground": "incompatible content grounds",
    "incompatible_content_explanation": "incompatible content explanation",
    "incompatible_content_illegal": "Yes",
    "content_type": [
        "CONTENT_TYPE_AUDIO",
        "CONTENT_TYPE_SYNTHETIC_MEDIA",
        "CONTENT_TYPE_VIDEO"
    ],
    "category": "STATEMENT_CATEGORY_CYBER_VIOLENCE_AGAINST_WOMEN",
    "territorial_scope": [
        "DE",
        "ES",
        "PT"
    ],
    "content_language": "EN",
    "content_date": "2023-08-08",
    "application_date": "2023-08-08",
    "end_date_monetary_restriction": "2023-08-08",
    "decision_facts": "facts about the decision",
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "automated_detection": "No",
    "automated_decision": "AUTOMATED_DECISION_PARTIALLY",
    "end_date_visibility_restriction": null,
    "end_date_account_restriction": null,
    "end_date_service_restriction": null,
    "uuid": "4b989c23-3736-4fd9-8612-a975b98d88d6",
    "created_at": "2024-11-25 09:16:55",
    "id": 34509873504987,
    "platform_name": "The Platform",
    "permalink": "https://.../statement/34509873504987",
    "self": "https://.../api/v1/statement/34509873504987",
    "puid": "TK421"
}
```

<x-ecl.message type="info" icon="information" title="Important" message="Anytime you make a call
to an API you should always validate that you did receive the proper status, '201 Created'.
If you did not receive a 201 Created, then the statement was not made, it is not in the database
and you will need to retry at a later time." close="" />

## UUID

Every statement created in the database receives an UUID which identifies the statement uniquely.

## ID

Every statement created in the database receives an ID which identifies the statement uniquely.

This ID is then used in the urls for retrieving and viewing the statement online.

These urls are present in the response after creating as the, "permalink" and "self" attributes.

## Creating Multiple Statements

We highly encourage all platforms to bundle and create multiple Statements of Reason in one API call using the multiple
endpoint.

Please to make a ```POST``` request to this endpoint.

<pre>
    {{route('api.v'.config('app.api_latest').'.statements.store')}}
</pre>

The payload of this request should contain one field called "statements" and that field
needs to be an array of Statements of Reason.

Here is an example:

```javascript
{
    "statements"
:
    [
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DISABLED"
            ],
            "decision_monetary": "DECISION_MONETARY_TERMINATION",
            "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
            ...
            ...
        },
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DISABLED"
            ],
            "decision_monetary": "DECISION_MONETARY_TERMINATION",
            "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
            ...
            ...
        },
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DISABLED"
            ],
            "decision_monetary": "DECISION_MONETARY_TERMINATION",
            "decision_provision": "DECISION_PROVISION_TOTAL_SUSPENSION",
            ...
            ...
        }
        ...
    ]
}
```

The multiple endpoint is capable of making 100 statements per call.

When the request has been sent and it is correct, a response of ```201``` ```Created``` will be
sent back.

The response payload when calling the multiple endpoint will be an array of the Statements of
Reason when successful. Each Statement of Reason will then have an
uuid, created_at, self, and permalink attribute to reflect that it was created.

```javascript
{
    "statements"
:
    [
        {
            "decision_visibility": [
                "DECISION_VISIBILITY_CONTENT_DEMOTED"
            ],
            "decision_monetary": "DECISION_MONETARY_OTHER",
            ...
            ...
            ...
                "uuid"
:
    "bf92a941-c77a-4b9d-a236-38956ae79cc5",
        "created_at"
:
    "2023-11-07 07:53:43",
        "platform_name"
:
    "The Platform",
        "puid"
:
    "b5ec958d-892a-4c11-a3f2-6a3ad597eeb1"
},
    {
        "decision_visibility"
    :
        [
            "DECISION_VISIBILITY_CONTENT_DEMOTED"
        ],
    ...
    ...
    ...
        "uuid"
    :
        "174a1921-0d9e-4864-b095-6774fb0237da",
            "created_at"
    :
        "2023-11-07 07:53:44",
            "platform_name"
    :
        "The Platform",
            "puid"
    :
        "a12b436a-33b1-4403-99b2-8c16e3c5502f"
    }
,
    {
        "decision_account"
    :
        "DECISION_ACCOUNT_SUSPENDED",
            "account_type"
    :
        "ACCOUNT_TYPE_PRIVATE",
            "decision_ground"
    :
        "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    ...
    ...
    ...
        "uuid"
    :
        "b8f03bf5-b8fd-4987-ac56-6fe6ab155e9e",
            "created_at"
    :
        "2023-11-07 07:53:45",
            "platform_name"
    :
        "The Platform",
            "puid"
    :
        "649c58f6-8412-4100-b10c-010b76f5a41a"
    }
,
...
]
}
```

## Statement Attributes

The attributes of the statement take on two main forms.

* free textual (max character limits apply, see below)
* limited, the value provided needs to be one of the allowed options

When submitting statements please take care to not submit ANY personal data. On a
regular basis we will do checks on the database to ensure that no personal data has been
submitted. However, in accordance with Article 24(5), it is the obligation of providers of online platforms to ensure
that the information submitted does not contain personal data.

## Additional Explanation For Statement Attributes

Please refer to
our [Additional Explanation For Statement Attributes](/page/additional-explanation-for-statement-attributes) page for
more information about the attributes.

### Decision Visibility (decision_visibility)

This attribute tells us the visibility restriction of specific items of information provided by the
recipient of the service.

This attribute is mandatory only if the following fields are empty: decision_monetary, decision_provision and
decision_account

The value provided must be an array with at least one of the following:

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

### Decision Visibility Other (decision_visibility_other)

This is required if DECISION_VISIBILITY_OTHER was the decision_visibility.

Limited to 500 characters.

### Monetary payments suspension, termination or other restriction (decision_monetary)

This is an attribute that gives information about the Monetary payments suspension, termination or other restriction

This attribute is mandatory only if the following fields are empty: decision_visibility, decision_provision and
decision_account

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

### Decision Monetary Other (decision_monetary_other)

This is required if DECISION_MONETARY_OTHER was the decision_monetary.

Limited to 500 characters.

### Decision about the provisioning of the service (decision_provision)

This is an attribute that tells us about the suspension or termination of the provision of the service.

This attribute is mandatory only if the following fields are empty: decision_visibility, decision_monetary and
decision_account

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

This attribute is mandatory only if the following fields are empty: decision_visibility, decision_monetary and
decision_provision

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
This is a text that explains why the content was illegal.

Limited to 2000 characters.

### Incompatible Content Grounds (incompatible_content_ground)

This is required if DECISION_GROUND_INCOMPATIBLE_CONTENT was the decision_ground.
It is the reference to contractual grounds.

Limited to 500 characters.

### Incompatible Content Explanation (incompatible_content_explanation)

This is required if DECISION_GROUND_INCOMPATIBLE_CONTENT was the decision_ground.
This is a text that explains why the content is considered as incompatible on that ground.

Limited to 2000 characters.

### Incompatible Content Illegal (incompatible_content_illegal)

This is an optional attribute and it can be in the form "Yes" or "No".
This is a possibility to indicate that the content was not only considered incompatible but also illegal.

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
It is a content type that is not part of provided content type list.

Limited to 500 characters.

### Category (category)

This is a required attribute, and it tells us which category the statement belongs to.

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_ANIMAL_WELFARE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Animal welfare</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_CONSUMER_INFORMATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Consumer information infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_CYBER_VIOLENCE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber violence</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_CYBER_VIOLENCE_AGAINST_WOMEN
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber violence against women</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_DATA_PROTECTION_AND_PRIVACY_VIOLATIONS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Data protection and privacy violations</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Illegal or harmful speech</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_INTELLECTUAL_PROPERTY_INFRINGEMENTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Intellectual property infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_NEGATIVE_EFFECTS_ON_CIVIC_DISCOURSE_OR_ELECTIONS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Negative effects on civic discourse or elections</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_NOT_SPECIFIED_NOTICE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Type of alleged illegal content not specified by the notifier</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_OTHER_VIOLATION_TC
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Other violation of provider’s terms and conditions</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_PROTECTION_OF_MINORS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Protection of minors</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_RISK_FOR_PUBLIC_SECURITY
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Risk for public security</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_SCAMS_AND_FRAUD
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Scams and/or fraud</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_SELF_HARM
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Self-harm</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_UNSAFE_AND_PROHIBITED_PRODUCTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Unsafe, non-compliant or prohibited products</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_VIOLENCE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Violence</li>
    </ul>
  </li>
</ul>

### Additional Categories (category_addition)

This is an optional attribute, and it tells us which additional categories the statement belongs to.

The value provided must be one of the following:

<ul class='ecl-unordered-list'>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_ANIMAL_WELFARE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Animal welfare</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_CONSUMER_INFORMATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Consumer information infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_CYBER_VIOLENCE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber violence</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_CYBER_VIOLENCE_AGAINST_WOMEN
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber violence against women</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_DATA_PROTECTION_AND_PRIVACY_VIOLATIONS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Data protection and privacy violations</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Illegal or harmful speech</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_INTELLECTUAL_PROPERTY_INFRINGEMENTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Intellectual property infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_NEGATIVE_EFFECTS_ON_CIVIC_DISCOURSE_OR_ELECTIONS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Negative effects on civic discourse or elections</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_NOT_SPECIFIED_NOTICE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Type of alleged illegal content not specified by the notifier</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_OTHER_VIOLATION_TC
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Other violation of provider’s terms and conditions</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_PROTECTION_OF_MINORS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Protection of minors</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_RISK_FOR_PUBLIC_SECURITY
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Risk for public security</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_SCAMS_AND_FRAUD
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Scams and/or fraud</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_SELF_HARM
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Self-harm</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_UNSAFE_AND_PROHIBITED_PRODUCTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Unsafe, non-compliant or prohibited products</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    STATEMENT_CATEGORY_VIOLENCE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Violence</li>
    </ul>
  </li>
</ul>

### Category Specification (category_specification)

This is an optional attribute, and it tells us which additional keywords the statement belongs to.

The value provided must be an array with one or more of the following:

<ul class='ecl-unordered-list'>
  <li class='ecl-unordered-list__item'>
    KEYWORD_ANIMAL_HARM
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Animal harm</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_ADULT_SEXUAL_MATERIAL
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Adult sexual material</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_AGE_SPECIFIC_RESTRICTIONS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Age-specific restrictions</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_AGE_SPECIFIC_RESTRICTIONS_MINORS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Age-specific restrictions concerning minors</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_BIOMETRIC_DATA_BREACH
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Biometric data breach</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_BULLYING_AGAINST_GIRLS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber bullying and intimidation against girls</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CHILD_SEXUAL_ABUSE_MATERIAL
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Child sexual abuse material</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CHILD_SEXUAL_ABUSE_MATERIAL_DEEPFAKE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Child sexual abuse material containing deepfake or similar technology</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CONTENT_PROMOTING_EATING_DISORDERS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Content promoting eating disorders</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_COORDINATED_HARM
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Coordinated harm</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_COPYRIGHT_INFRINGEMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Copyright infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CYBER_BULLYING_INTIMIDATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber bullying and intimidation</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CYBER_HARASSMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber harassment</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CYBER_HARASSMENT_AGAINST_WOMEN
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber harassment against women</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CYBER_INCITEMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber incitement to hatred or violence</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CYBER_STALKING
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber stalking</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_CYBER_STALKING_AGAINST_WOMEN
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Cyber stalking against women </li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_DATA_FALSIFICATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Data falsification</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_DEFAMATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Defamation</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_DESIGN_INFRINGEMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Design infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_DISCRIMINATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Discrimination</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_MISINFORMATION_DISINFORMATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Misinformation, disinformation, foreign information manipulation and interference</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_FEMALE_GENDERED_DISINFORMATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Gendered disinformation</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_GEOGRAPHIC_INDICATIONS_INFRINGEMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Geographic indications infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_GEOGRAPHICAL_REQUIREMENTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Geographical requirements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_GOODS_SERVICES_NOT_PERMITTED
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Goods/services not permitted to be offered on the platform</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_GROOMING_SEXUAL_ENTICEMENT_MINORS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Grooming/sexual enticement of minors</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_HATE_SPEECH
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Illegal incitement to violence and hatred based on protected characteristics (hate speech)</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_HIDDEN_ADVERTISEMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Hidden advertisement or commercial communication, including by influencers</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_HUMAN_EXPLOITATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Human exploitation</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_HUMAN_TRAFFICKING
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Human trafficking</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_ILLEGAL_ORGANIZATIONS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Illegal organizations</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_IMPERSONATION_ACCOUNT_HIJACKING
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Impersonation or account hijacking</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_INAUTHENTIC_ACCOUNTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Inauthentic accounts</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_INAUTHENTIC_LISTINGS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Inauthentic listings</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_INAUTHENTIC_USER_REVIEWS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Inauthentic user reviews</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_INCITEMENT_AGAINST_WOMEN
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Illegal incitement to violence and hatred against women</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_INCITEMENT_VIOLENCE_HATRED
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>General calls or incitement to violence and/or hatred</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_INSUFFICIENT_INFORMATION_ON_TRADERS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Insufficient information on traders</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_LANGUAGE_REQUIREMENTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Language requirements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_MISLEADING_INFO_CONSUMER_RIGHTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Misleading information about the consumer’s rights</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_MISLEADING_INFO_GOODS_SERVICES
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Misleading information about the characteristics of the goods and services</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_MISSING_PROCESSING_GROUND
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Missing processing ground for data</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_NON_CONSENSUAL_IMAGE_SHARING
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Non-consensual (intimate) material sharing, including (image-based) sexual abuse (excluding content depicting minors)</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_NON_CONSENSUAL_IMAGE_SHARING_AGAINST_WOMEN
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Non-consensual (intimate) material sharing against women, including (image-based) sexual abuse against women (excluding content depicting minors)</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_NON_CONSENSUAL_MATERIAL_DEEPFAKE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Non-consensual sharing of material containing deepfake or similar technology using a third party's features (excluding content depicting minors)</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_NON_CONSENSUAL_MATERIAL_DEEPFAKE_AGAINST_WOMEN
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Non-consensual sharing of material containing deepfake or similar technology using a third party's features against women (excluding content depicting minors)</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_NONCOMPLIANCE_PRICING
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Non-compliance with pricing regulations</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_NUDITY
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Nudity</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_PATENT_INFRINGEMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Patent infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_PHISHING
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Phishing</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_PROHIBITED_PRODUCTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Prohibited or restricted products</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_PYRAMID_SCHEMES
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Pyramid schemes</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_RIGHT_TO_BE_FORGOTTEN
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Right to be forgotten</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_OTHER
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Not captured by any other keyword</li>
    </ul>
  </li>



  <li class='ecl-unordered-list__item'>
    KEYWORD_RISK_ENVIRONMENTAL_DAMAGE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Risk for environmental damage</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_RISK_PUBLIC_HEALTH
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Risk for public health</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_SELF_MUTILATION
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Self-mutilation</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_STALKING
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Stalking</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_SUICIDE
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Suicide</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_TERRORIST_CONTENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Terrorist content</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_TRADE_SECRET_INFRINGEMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Trade secret infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_TRADEMARK_INFRINGEMENT
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Trademark infringements</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_TRAFFICKING_WOMEN_GIRLS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Trafficking in women and girls</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_UNLAWFUL_SALE_ANIMALS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Unlawful sale of animals</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_UNSAFE_CHALLENGES
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Unsafe challenges</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_UNSAFE_PRODUCTS
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Unsafe or non-compliant products</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_VIOLATION_EU_LAW
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Violation of EU law relevant to civic discourse or elections</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_VIOLATION_NATIONAL_LAW
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Violation of national law relevant to civic discourse or elections</li>
    </ul>
  </li>
  <li class='ecl-unordered-list__item'>
    KEYWORD_OTHER
    <ul class='ecl-unordered-list'>
      <li class='ecl-unordered-list__item'>Not captured by any other keyword</li>
    </ul>
  </li>
</ul>

### Content identifier (content_id)

This is an optional attribute, which allows to track existing identifiers of illegal content in key-value format.

The attribute must be provided in key-value format.

Currently, the only foreseen key that will be accepted is “EAN-13”, with which a product identifier in the form of an
EAN-13 code can be submitted as a value.

### Other Keyword (category_specification_other)

This field can be provided if KEYWORD_OTHER is part of the category_specification.

Limited to 500 characters.

### Territorial Scope (territorial_scope)

This is a required attribute that defines territorial scope of the restriction. Each value must be the 2-letter iso code
for the country and the countries must be (EU/EEA) countries.

The value provided must be an array.

Allowed values are:

@php echo implode(', ', \App\Services\EuropeanCountriesService::EUROPEAN_COUNTRY_CODES); @endphp

For European Union (EU) use:

@php echo '["' . implode('", "', \App\Services\EuropeanCountriesService::EUROPEAN_UNION_COUNTRY_CODES) . '"]'; @endphp

For European Economic Area (EEA) use:

@php echo '["' . implode('", "', \App\Services\EuropeanCountriesService::EUROPEAN_ECONOMIC_AREA_COUNTRY_CODES) . '"]';
@endphp

### Content Language (content_language)

This is the language that the content was in.

This attribute is optional.

The value though must be one of the uppercase two
letter [ISO 639-1](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) codes.

Ex,

@php echo '"' . implode('", "', \App\Services\EuropeanLanguagesService::EUROPEAN_LANGUAGE_CODES) . '"'; @endphp

### Content Date (content_date)

This is a required date field that indicates the upload or posting date of the content. The date should follow this
format:

```YYYY-MM-DD```

The day and the month have leading zeroes.

The date must be after or equal to 2000-01-01.

### Application Date (application_date)

This is the date that this decision starts from. The date needs to take the form of:

```YYYY-MM-DD```

The day and the month have leading zeroes.

The date must be after or equal to 2020-01-01.

### End Date of account restriction (end_date_account_restriction)

This is the date that the decision on the account ends. Leave blank for indefinite.

The date needs to take the form of:

```YYYY-MM-DD```

The day and the month have leading zeroes.

The date must be after or equal to the application date.

### End Date of monetary restriction (end_date_monetary_restriction)

This is the date that the monetary decision ends. Leave blank for indefinite.

The date needs to take the form of:

```YYYY-MM-DD```

The day and the month have leading zeroes.

The date must be after or equal to the application date.

### End Date of service restriction (end_date_service_restriction)

This is the date that the provision decision ends. Leave blank for indefinite.

The date needs to take the form of:

```YYYY-MM-DD```

The day and the month have leading zeroes.

The date must be after or equal to the application date.

### End Date of visibility restriction (end_date_visibility_restriction)

This is the date that the visibility decision ends. Leave blank for indefinite.

The date needs to take the form of:

```YYYY-MM-DD```

The day and the month have leading zeroes.

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

### Source Identity (source_identity)

This is an optional field to describe the source/notifier if needed. Will not be taken into account if the 'source_type'
is set to 'SOURCE_VOLUNTARY'

Limited to 500 characters.

### Automated Detection (automated_detection)

This is a required attribute and it must be in the form "Yes" or "No".
This indicates to us that decision taken in respect of automatically detected means.

### Automated Decision (automated_decision)

This is a required attribute and it must be one of the following:

<ul class='ecl-unordered-list'>
@php
    foreach (\App\Models\Statement::AUTOMATED_DECISIONS as $key => $value) {
        echo "<li class='ecl-unordered-list__item'>";
        echo $key;
        echo "<ul class='ecl-unordered-list'><li class='ecl-unordered-list__item'>" . $value . "</li></ul>";
        echo "</li>\n";
    }
@endphp
</ul>

### Platform Unique Identifier (puid)

This is a string that uniquely identifies this statement within the platform. This attribute is required and it must be
unique within your platform.

Limited to 500 characters and must contain alphanumeric characters (a-z, A-Z, 0-9), hyphens "-" and underscores "_"only.
No spaces, new-line or any other special characters are accepted.

## Existing PUID

This endpoint allows you to determine whether a given PUID (Persistent Unique Identifier) is already associated with a Statement of Reason (SoR).

To check if an existing PUID is already used in a statement of reason using the API you will need to make a
```GET``` request to this endpoint.

<pre>
    {{route('api.v'.config('app.api_latest').'.statement.existing-puid', ['puid' => '&lt;PUID&gt;'])}}
</pre>

Replace ```<PUID>``` with the actual PUID you want to check.

### Required Headers

<pre>
    Authorization: Bearer YOUR_TOKEN
    Accept: application/json
    Content-Type: application/json
</pre>

### Responses

#### 1. SoR Not Found

* HTTP Status: ```404 Not Found```
* Response Body

```javascript
{
    "message": "statement of reason not found",
    "puid": "YOUR_PUID"
}
```

#### 2. SoR Found

* HTTP Status: ```302 Found```.
* Response Body

```javascript
{
    "message": "statement of reason found", 
    "puid": "YOUR_PUID"
}
```

## Errors

When a call to the API has been made AND there was an error in the call you may
expect the following to occur:

- You will NOT receive a HTTP Status Code ```201 Created```.
- The statement of reason has NOT been created.
- You receive back a payload that has more information in it.

For Ex,

You made an API with a blank JSON payload or an invalid JSON payload.

```javascript
{
}
```

The HTTP Status code coming back will be ```422 Unproccessable Content```

The payload body will be a JSON object containing more information and the errors in the API call.

```javascript
{
    "message"
:
    "The decision visibility field is required when none of decision monetary / decision provision / decision account are present. (and 13 more errors)",
        "errors"
:
    {
        "decision_visibility"
    :
        [
            "The decision visibility field is required when none of decision monetary / decision provision / decision account are present."
        ],
            "decision_monetary"
    :
        [
            "The decision monetary field is required when none of decision visibility / decision provision / decision account are present."
        ],
            "decision_provision"
    :
        [
            "The decision provision field is required when none of decision visibility / decision monetary / decision account are present."
        ],
            "decision_account"
    :
        [
            "The decision account field is required when none of decision visibility / decision monetary / decision provision are present."
        ],
            "decision_ground"
    :
        [
            "The decision ground field is required."
        ],
            "content_type"
    :
        [
            "The content type field is required."
        ],
            "category"
    :
        [
            "The category field is required."
        ],
            "application_date"
    :
        [
            "The application date field is required."
        ],
            "decision_facts"
    :
        [
            "The decision facts field is required."
        ],
            "source_type"
    :
        [
            "The source type field is required."
        ],
            "automated_detection"
    :
        [
            "The automated detection field is required."
        ],
            "automated_decision"
    :
        [
            "The automated decision field is required."
        ],
            "puid"
    :
        [
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
    "message"
:
    "The selected automated decision is invalid.",
        "errors"
:
    {
        "automated_decision"
    :
        [
            "The selected automated decision is invalid."
        ]
    }
}
```

### Errors when Creating Multiple Statements of Reason

When you are you calling the multiple endpoint you will encounter the same errors as the single endpoint.
However, the errors will be indexed to the Statement of Reason that you are trying to create.

ex,

```javascript
{
    "errors"
:
    {
        "statement_0"
    :
        {
            "decision_monetary"
        :
            [
                "The selected decision monetary is invalid."
            ],
                "decision_ground"
        :
            [
                "The selected decision ground is invalid."
            ],
                "automated_detection"
        :
            [
                "The automated detection field is required."
            ]
        }
    ,
        "statement_2"
    :
        {
            "decision_provision"
        :
            [
                "The selected decision provision is invalid."
            ]
        }
    }
}
```

This means that the decision monetary, the decision ground and the automated detection fields were invalid in the
statement of reason at position 0 in the array.
This means that the decision provision is invalid in the statement of reason at position 2 in the array.

In this case, **NONE** of the statements where created, the request needs to be fixed and resent.

### Token Error

Another common error that may occur when calling the API is that the authorization token is not valid.

This will result in a HTTP status code of ```401 Unauthorized```

The API authorization token needs to be double checked or a new API authorization token needs to be
generated. See again the section above: [Your API Token](#your-api-token)

In addition to the common ```422``` and ```401``` errors, Any of the standard 4XX HTTP can be
encountered. 4XX statuses generally indicate that there is an issue with your request. Please try to
troubleshoot and resolve the problem.

When there is an error of 5XX we are immediately notified and there is no need
to report the issue.

### PUID Error

When you attempt to create a statement for your platform and there exists a statement with the same puid, the
response will still be ```422 Unproccessable Content``` and the error returned will contain the existing
the statement. This will look like the following:

```javascript
{
    "message"
:
    "The identifier given is not unique within this platform.",
        "errors"
:
    {
        "puid"
    :
        [
            "The identifier given is not unique within this platform."
        ]
    }
,
    "existing"
:
    {
        "uuid"
    :
        "6bf8beb0-765c-4e79-8cb1-dc93fc7478bb",
            "decision_visibility"
    :
        [
            ...
        ],
    ...
        "permalink"
    :
        "... /statement/6bf8beb0-765c-4e79-8cb1-dc93fc7478bb",
            "self"
    :
        "... /api/v1/statement/6bf8beb0-765c-4e79-8cb1-dc93fc7478bb"
    }
}
```

## Source Code

The source code for this application can be viewed here:

[DSA Transparency Database Source - GitHub](https://github.com/digital-services-act/transparency-database)

Using the repository code you can even setup and run a local replica development testing area.

Within the github environment you are also more than welcome to give pull requests and
reviews concerning the source code. 
