## Explanation of the information held in the DSA Transparency Database

The DSA Transparency Database is structured in a way that enables transparency and scrutiny over the content moderation
decisions of online platforms. This page explains what type of information is collected, and how the different data
fields map onto Article 17 DSA, which lays down the requirements of sending statements of reasons to recipients of the
service.

## Submission of clear and specific statements

This sub-category consists of the following datapoints:

[1. A Platform Unique Identifier (PUID)](#1-platform-unique-identifier-puid)

[2. Specifications on the content that is affected by the statement](#2-specifications-of-the-content-affected-by-the-decision)

_Why is this information included?_

_Article 17(1) and 17(4) DSA require the statements of reasons submitted to the database to be clear and specific. In
addition, characteristics of the content affected by the content moderation decision are part of the facts and
circumstances referred to in Article 17(3)(b) of the DSA. The requirements under this first sub-category also increase
the database’s function to ensure transparency and to enable scrutiny over content moderation decisions in line with
recital 66 of the DSA._

### 1. Platform Unique Identifier (PUID)

This is a string that uniquely identifies a statement of reasons within the online platform’s systems.

The PUID is assigned by the given online platform, and it is unique to each statement of reasons. This allows the online
platforms to identify the exact information that was affected by the content moderation decision and offers the
opportunity to connect the data in this database with additional information, such as the specific URL, for example.

### 2. Specifications of the content affected by the decision

The specifications of the content affected by the decision cover the required fields regarding the type of content
affected and the content creation date, as well as the optional field concerning the language of the affected
information.

#### 2.1. Type of content affected

This data field describes the type of content that is restricted by the decision to which the statement of reasons
relates. Content can fall into more than one of the following categories: app, audio, image, product, synthetic media,
text and video. For example, a post on a social media platform could consist of text and image content. Similarly, a
video modified by (generative) AI tools could be described as both a video and synthetic media. If the specific options
do note describe the type of restricted content adequately, “Other” can also be selected and further specified.

#### 2.2. Specification of Content Type "Other"

If the selected option for the attribute content_type was content_type_other, a further specification is required. Only
those content types should be indicated here that are not included in the pre-defined list of content types.

#### 2.3. Date on which the content was created on the online platform

This attribute indicates the date of the creation of the content, i.e. when the online platform started hosting the
information affected by the decision. For example, this can be the date that specific content was posted, or the date
that a user account was registered.

#### 2.4. Language of the content

This attribute concerns the language of the information affected by the decision.

#### 2.5. Content identifier

This attribute allows to track existing content identifiers in key-value format for the identification of content according to existing content taxonomies, making it easier to systematically track illegal content, for example illegal products.

Currently, the only accepted key-value combination is a product identifier, namely the EAN-13 code. When “EAN-13” is submitted as a key, an EAN-13 code can be submitted as a value.

---

## Information on the type of restriction(s) imposed, on the territorial scope, and the duration of the restriction

This sub-category consists of the following datapoints:

[3. The type of restrictions imposed](#3-the-type-of-restrictions-imposed)

[4. The duration of the restriction](#4-the-duration-of-the-decision)

[5. The territorial scope of the restriction](#5-the-territorial-scope-of-the-decision)

_Why is this information included?_

_Article 17(3) DSA sets out minimum requirements for the information that is to be included in a statement of reasons.
This includes information on the type of restriction(s) imposed as well as their territorial scope and duration, as
specified in Article 17(3)(a) DSA. In accordance with Article 17(1) (a)-(d) DSA, the types of restrictions included are
visibility restrictions, monetary payment restrictions, service restrictions and account restrictions._

### 3. THE TYPE OF RESTRICTION(S) IMPOSED

Each statement of reasons needs to include at least one decision regarding a restriction imposed by the online platform.
One of the four types of restrictions (visibility, monetary, provision of the service, service’s account) mentioned in
article 17(1)(a)-(d) will therefore always be indicated.

However, one decision and statement of reasons can contain multiple restrictions. For example, a post on a social media
platform that has been deemed allegedly illegal under a particular national law could be subject to a visibility
restriction and lead to a temporary suspension of the recipient of the service’s account. Similarly, a counterfeit
product offered on a marketplace could be subject to a visibility restriction as well as a restriction of monetary
payments. It is therefore possible for each statement of reasons to include multiple types of restriction decisions.

#### 3.1. Decision to restrict visibility

This attribute describes the visibility restriction(s) imposed on specific items of information provided by the
recipient of the service. The restriction can fall into one or more than one of the following categories: Removal of
content, disabling access to content, demotion of content, age restriction of content, interaction restriction of
content and labelling of content. If none of these options adequately describe the visibility restriction(s) imposed by
the statement of reasons, the online platform needs to select “Other”.

#### 3.2. Specification of other visibility restriction

When the decision contains a visibility restriction that is different from one of the specific restrictions offered by
the submission form, then this field indicates the nature of the restriction.

#### 3.3. Decision to restrict monetary payments

This attribute describes the monetary payment restriction imposed on specific items of information provided by the
recipient of the service. The restriction can fall into one of the following categories: Suspension of monetary payments
or termination of monetary payments. If none of these options adequately describe the monetary restriction(s) imposed by
the online platform, “Other” will be selected.

#### 3.4. Specification of other monetary restriction

When the decision contains a monetary restriction that is different from a monetary suspension or monetary termination
restriction, then this field is required to indicate the nature of the restriction.

#### 3.5. Decision to restrict the provisioning of the service

This attribute describes the restriction imposed on the provision of the service to a recipient of the service. The
restriction can fall into one of the following categories: Partial suspension of the provision of the service, total
suspension of the provision of the service, partial termination of the provision of the service, or total termination of
the provision of the service.

#### 3.6. Decision to restrict access to the account

This attribute describes the restriction imposed on the recipient of the service’s account. The restriction can fall
into one of the following categories: Suspension of the account or termination of the account.
___

### 4. THE DURATION OF THE RESTRICTION

Each restriction decision has a temporal scope determined by its start and end date, or lack of end date. If a statement
of reasons includes multiple restrictions, the duration can be different for each type of restriction imposed.

For example, a visibility restriction could be imposed indefinitely on a piece of content that was deemed allegedly
illegal. Simultaneously, the recipient of the service’s account could be suspended for three months for the same
infringement. In this case, the duration of the two restrictions imposed would be different, for they would have
different end dates.

Depending on the restrictions included in the statement of reasons, the relevant end date attribute values will be
provided. Where no end date is indicated, this means that the relevant restriction was imposed indefinitely.

Taking the example above, the end_date_visibility_restriction attribute would be blank, whereas the
end_date_account_restriction attribute would be set to a date three months after the application_date attribute.

#### 4.1. Application Date

This is the date, from which the restriction(s) applies/y.

#### 4.2. End date for visibility restrictions

This is the date, when the imposed visibility restriction ends.

#### 4.3. End date for monetary restrictions

This is the date when the imposed monetary restriction ends.

#### 4.4. End date for service restrictions

This is the date when the service restriction ends.

#### 4.5. End date for account restrictions

This is the date when the account restriction ends.
___ 

### 5. THE TERRITORIAL SCOPE OF THE DECISION

**Territorial scope**
This is the territorial scope of the restrictions imposed. Multiple or all EU or EEA countries can be indicated.

___

## Information on the facts and circumstances relied on in taking the decision

This sub-category consists of the following datapoints:

[6. A description of the facts and circumstances](#6-description-of-the-facts-and-circumstances)

[7. Information on the source of the investigation](#7-information-on-the-source-of-the-investigation)

[8. Information on the account affected by the decision](#8-information-on-the-account-affected-by-the-decision)

_Why is this information included?_

_Article 17(3) DSA sets out minimum requirements for the information that is to be included in a statement of reasons.
This includes the facts and circumstances relied on in taking the decision, as specified in Article 17(3)(b) DSA._

### 6. DESCRIPTION OF THE FACTS AND CIRCUMSTANCES

The facts and circumstances of each content moderation may be different. Apart from the type, date and language of the
content, online platforms need to provide adequate information which they relied on when taking the decision. It is
important that, in line with Article 24(5) DSA, online platforms should not include personal data in their submissions.

**Facts and circumstances relied on in taking the decision**

This is a free text field to describe the facts and circumstances relied on in taking the decision.

### 7. INFORMATION ON THE SOURCE OF THE INVESTIGATION

#### 7.1. Information source

This attribute describes what led to the investigation of the content, which is part of the facts and circumstances
relied on in taking the decision. The source of the investigation can fall into one of the following categories: An
investigation can be based on a notice submitted in accordance with Article 16 DSA; a notice submitted by a trusted
flagger under the DSA; any other external notification; or the online platform’s own voluntary initiative.

#### 7.2. The identity of the notifier

In accordance with Article 17(3)(b) DSA, the identity of the notifier needs to be included in the statement of reasons,
but only if that is strictly necessary to identify the illegality of the content. This can be the case, for example, for
infringements of intellectual property rights.

Even in such cases, providers of online platforms shall ensure that the information submitted does not contain personal
data, in accordance with Article 24(5) DSA.

### 8. INFORMATION ON THE ACCOUNT AFFECTED BY THE DECISION

**Account type**

This attribute concerns the nature of the account connected to the information addressed by the decision. It indicates
whether the account holder is a business user within the meaning of Regulation (EU) 2019/1150 or the account was a
private account, meaning an account for personal use only.

___

## Information on the use made of automated means

This sub-category consists of the following datapoints:

[9. Information on the use made of automated means for the detection of infringements](#9-automated-detection)

[10. Information on the use made of automated means for taking decisions on infringements](#10-automated-decision)

_Why is this information included?_

_Article 17(3)(c) of Regulation 2022/2065 (DSA) requires the parties that submit statements of
reasons to provide information on the use made of automated means in taking the decision,
including information on whether the decision was taken in respect of content detected or
identified using automated means._

### 9. Automated Detection

This attribute indicates whether and to what extent automated means were used to identify the specific information
addressed by the decision. ‘Yes’ means that automated means were used to identify the specific information addressed by
the decision.

### 10. Automated Decision

This attribute indicates whether and to what extent automated means were used to decide on the infringing nature of the
specific information addressed by the decision. ‘Fully automated’ means that the entire decision-process was carried out
without human intervention. This attribute does not refer to the identification of such content, but solely to the
decision taken after the identification. ‘Not automated’ means that the entire decision-process was carried out without
the use of automated means. ‘Partially automated’ means that both automated means and human interaction were applied in
the process of taking a decision regarding the infringing nature of the information.

___

## The legal or contractual grounds relied on in taking the decision

This sub-category consists of the following datapoints:

[11. The decision grounds](#11-decision-grounds-decision-ground)

[12. For allegedly illegal information: the legal ground relied upon](#12-for-allegedly-illegal-information-the-legal-ground-relied-upon)

[13. For allegedly infringing information: the contractual ground relied upon](#13-for-allegedly-incompatible-information-the-contractual-ground-relied-upon)

[14. Overlap between incompatibility and illegality](#14-overlap-between-tos-incompatibility-and-illegality-incompatible-content-illegal)

[15. Reference (URL) to the legal or contractual ground](#15-reference-url-to-the-legal-or-contractual-ground-decision-ground-reference-url)

[16. Category & Category specification](#16-category-specification-category-category-addition-category-specification)

_Why is this information included?_

_Article 17(3)(d) and (e) DSA require the parties that submit statements of reasons to include a reference to the legal
ground or the contractual ground relied on when taking the decision. This requirement also includes an explanation as to
why the information is considered to be illegal or incompatible with the referenced ground. A category indicating the
type of illegality, or the type of incompatibility with the service’s terms and conditions, based on which the content
was moderated, must be selected. The categories allow queries for information necessary to enable scrutiny over content
moderation decisions.  _

### 11. Decision Grounds

This attribute indicates whether the decision was taken in line with article 17(3)(d) DSA, meaning that the information
was allegedly illegal, or in line with article 17(3)(e) DSA, meaning that the information was allegedly incompatible
with the service’s terms and conditions.

### 12. For allegedly illegal information: the legal ground relied upon

##### 12.1. Reference to the legal ground

This is a field where the exact legal ground (i.e. the applicable law(s)) that was/were relied upon in taking the
decision must be stated.

##### 12.2. Explanation of the applicability of the legal ground

This is a field to explain why the information is considered illegal on the basis of the legal ground indicated. The
explanation does not have to repeat the facts and circumstances but can refer to those.

### 13. For allegedly incompatible information: the contractual ground relied upon

#### 13.1. Incompatible Content Grounds

This is a field where the exact contractual ground (i.e. the relevant section in the applicable terms and conditions)
that was relied upon in taking the decision must be stated.

#### 13.2. Incompatible Content Explanation

This is a field to explain as to why the information is considered incompatible with a specific section in the service’s
terms and conditions. The explanation does not have to repeat the facts and circumstances but can refer to those.

### 14. Overlap between TOS incompatibility and illegality

Some information that is incompatible with the terms and conditions of a service can simultaneously be illegal. This
optional field allows to indicate whether information that was restricted on the basis of an alleged incompatibility
with the terms and conditions was also considered illegal by the online platform. Leaving this optional field blank does
not imply that the online platform considers that the content is not illegal.

### 15. Reference (URL) to the legal or contractual ground

Where a specific URL to the legal or contractual ground is available, it is encouraged to include
these to allow for a quick identification of the ground that was invoked to take the decision.

Examples are a direct URL to the applicable version of the terms and conditions relied on in
taking the decision, or a direct URL to the applicable law relied on to take a decision on the
alleged illegality of information. An example of the direct reference to the DSA is:
[https://eur-lex.europa.eu/eli/reg/2022/2065](https://eur-lex.europa.eu/eli/reg/2022/2065).

### 16. CATEGORY & SPECIFICATION

A list of categories and specifications are included to codify the type of illegality and/or the type of incompatibility with terms and conditions that led to the restriction of the information. The available categories and specifications correspond to those set out in the [implementing regulation laying down templates concerning the transparency reporting obligations of providers of online platforms](https://digital-strategy.ec.europa.eu/en/library/implementing-regulation-laying-down-templates-concerning-transparency-reporting-obligations) to ensure consistency between the transparency tools of the DSA. The category and specifications section consists of three attributes:

Firstly, a high-level category classification must be indicated. The high-level classification indicates the main
category under which the grounds relied on in a statement of reasons fall. The list of high-level categories is
exhaustive, and a single selection is required. The option that best covers the grounds based on which the decision was
taken should be selected by the online platform.

For cases in which more granularity is required, two additional – optional – attributes can be used to further clarify
the category in question. The additional category attribute provides a list of category options identical to the
high-level category classification. This list is also exhaustive, but it allows for multiple selection. Lastly, the
category specification attribute allows for the addition of keywords that further specify the high-level categories.
Platforms are encouraged to use as many additional category and specification options as necessary to describe the
grounds relied on in taking the decision as precise and specific as reasonably possible. The category specification
field also contains an “Other” option, which can be further specified. The lists have been designed so that the
specifications fall under the high-level categories as follows (listed in alphabetical order):

- Animal welfare
    - Animal harm
    - Unlawful sale of animals

- Consumer information infringements
  - Hidden advertisement or commercial communication, including by influencers
  - Insufficient information on traders
  - Misleading information about the characteristics of the goods and services
  - Misleading information about the consumer’s rights
  - Non-compliance with pricing regulations

- Cyber violence
  - Cyber bullying and intimidation
  - Cyber harassment
  - Cyber incitement to hatred or violence
  - Cyber stalking
  - Non-consensual (intimate) material sharing, including (image-based) sexual abuse (excluding content depicting minors)
  - Non-consensual sharing of material containing deepfake or similar technology using a third party's features (excluding content depicting minors)

- Cyber violence against women
  - Cyber bullying and intimidation against girls
  - Cyber harassment against women
  - Cyber stalking against women
  - Gendered disinformation
  - Illegal incitement to violence and hatred against women
  - Non-consensual (intimate) material sharing against women, including (image-based) sexual abuse against women (excluding content depicting minors)
  - Non-consensual sharing of material containing deepfake or similar technology using a third party's features against women (excluding content depicting minors)

- Data protection and privacy violations
  - Biometric data breach
  - Data falsification
  - Missing processing ground for data
  - Right to be forgotten
- Illegal or harmful speech
  - Defamation
  - Discrimination
  - Illegal incitement to violence and hatred based on protected characteristics (hate speech)
- Intellectual property infringements
  - Copyright infringement
  - Design infringement
  - Geographical indications infringements
  - Patent infringement
  - Trade secret infringement
  - Trademark infringement
- Negative effects on civic discourse or elections
  - Misinformation, disinformation, foreign information manipulation and interference
  - Violation of EU law relevant to civic discourse or elections
  - Violation of national law relevant to civic discourse or elections
- Other violation of provider's terms and conditions
  - Adult sexual material
  - Age-specific restrictions
  - Geographical requirements
  - Goods/services not permitted to be offered on the platform
  - Language requirements
  - Nudity
- Protection of minors
  - Age-specific restrictions concerning minors
  - Child sexual abuse material
  - Child sexual abuse material containing deepfake or similar technology
  - Grooming/sexual enticement of minors
  - Unsafe challenges
- Risk for public security
  - Illegal organizations
  - Risk for environmental damage
  - Risk for public health
  - Terrorist content
- Scams and/or fraud
  - Impersonation or account hijacking
  - Inauthentic accounts
  - Inauthentic listings
  - Inauthentic user reviews
  - Phishing
  - Pyramid schemes
- Self-harm
  - Content promoting eating disorders
  - Self-mutilation
  - Suicide
- Type of alleged illegal content not specified by the notifier
- Unsafe, non-compliant or prohibited products
  - Prohibited or restricted products
  - Unsafe or non-compliant products
- Violence
  - Coordinated harm
  - General calls or incitement to violence and/or hatred
  - Human exploitation
  - Human trafficking
  - Trafficking in women and girls
- Not captured by any high-level category
  - Not captured by any other keyword

For example, if an unsafe product was removed from an online marketplace that was also a counterfeit good, the relevant statement of reasons could indicate either “Unsafe, non-compliant or prohibited products” or “Intellectual property infringements” as the main category, depending on which one was considered more relevant to the decision by the platform. The respective other option could be chosen in attribute_addition, and one or more of the relevant category_specification options (e.g. “Trademark infringement” and/or “Prohibited or restricted products ” or other category_specification options considered relevant by the platform) could be added to provide additional information. Platforms are encouraged to use as many options as they see fit to best describe their statements of reasons with these data points.

#### 16.1. Field to add an open specification

For those statements of reasons where platforms feel that no existing specification properly
reflects the grounds they used, this section allows for a free text submission to add
specifications. The Commission will monitor the submissions in this free text field and update
the options in category_specification accordingly, should recurring submissions prevail. 

 

 
