# Changelog

##  [v1.0] - 2023-08-16
### Added

### Changed
- Source field is now optional


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

