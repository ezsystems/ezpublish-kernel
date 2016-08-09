# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Changes to use of `eZ\Publish\API\Repository\Exceptions\ContentValidationException`

  ContentService has as of https://jira.ez.no/browse/EZP-24853 received some cleanup in
  regards to when `ContentValidationException` vs `ContentFieldValidationException` is used.
  The following use of `ContentValidationException` has moved to `ContentFieldValidationException`:
  - if a required field is missing / set to an empty value

  This implies REST/PHP API will now provide full list of validation errors, including empty fields as
  `ContentFieldValidationException`.


## Deprecations




## Removed features

