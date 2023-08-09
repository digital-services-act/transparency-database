# Changelog

##  [v1.0] - 2023-08-16
### Added
- New API field: 'category_addition'
- New values to the API field: 'content_type'
  - CONTENT_TYPE_APP
  - CONTENT_TYPE_PRODUCT
  - CONTENT_TYPE_AUDIO
  - CONTENT_TYPE_SYNTHETIC_MEDIA
- New value to the API field: 'source_type'
  - SOURCE_OTHER

 
### Changed
- Renamed the API field: start_date to application_date
- Renamed the API field: countries_list to territorial_scope
- Modified the API field: 'source' is now optional
- Modified the API field: 'incompatible_content_illegal' is now optional
- Modified the API field: 'content_type' is now an array
- Modified the values for API field 'category'
  - STATEMENT_CATEGORY_ANIMAL_WELFARE
  - STATEMENT_CATEGORY_DATA_PROTECTION_AND_PRIVACY_VIOLATIONS
  - STATEMENT_CATEGORY_ILLEGAL_OR_HARMFUL_SPEECH
  - STATEMENT_CATEGORY_INTELLECTUAL_PROPERTY_INFRINGEMENTS
  - STATEMENT_CATEGORY_NEGATIVE_EFFECTS_ON_CIVIC_DISCOURSE_OR_ELECTIONS
  - STATEMENT_CATEGORY_NON_CONSENSUAL_BEHAVIOUR
  - STATEMENT_CATEGORY_PORNOGRAPHY_OR_SEXUALIZED_CONTENT
  - STATEMENT_CATEGORY_PROTECTION_OF_MINORS
  - STATEMENT_CATEGORY_RISK_FOR_PUBLIC_SECURITY
  - STATEMENT_CATEGORY_SCAMS_AND_FRAUD
  - STATEMENT_CATEGORY_SELF_HARM
  - STATEMENT_CATEGORY_SCOPE_OF_PLATFORM_SERVICE
  - STATEMENT_CATEGORY_UNSAFE_AND_ILLEGAL_PRODUCTS
  - STATEMENT_CATEGORY_VIOLENCE


### Deleted
- Removed the API field: 'url'

### Fixed


##  [v0.1] - 2023-07-17
### Added
- Added a new mandatory field to the API: PUID (Platform Unique Identifier).
- Included a section in the documentation explaining error codes.
- Provided information in the documentation regarding the maximum length of free text fields.
- Added a section in the documentation that explains how to request API access.

### Changed
- Modified the API field: URL can now contain text and is no longer validated strictly as a URL.
- Limited the API field: decision_facts to 5000 characters.
- Limited the API field: DECISION_GROUND_ILLEGAL_CONTENT to 2000 characters.
- Limited the API field: DECISION_GROUND_INCOMPATIBLE_CONTENT to 2000 characters.

### Fixed
- Corrected a typo in the Statement.php file, which was causing incorrect value return.

