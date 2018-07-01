# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

- EZP-27420: `SPI\Persistence\VersionInfo->languageIds` has been removed and replaced by property `languageCodes`

  Change is done to reduce complexity, make it consistent with other language properties on Content aggregate,
  and slightly optimize mapping of API VersionInfo objects.

## Deprecations

## Removed features
