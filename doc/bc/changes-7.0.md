# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* SPI: eZ\Publish\SPI\Persistence\User::deletePolicy adds role id argument
  Id of Role is added on deletePolicy() to be able to properly clear cache
  for the affected role.


## Deprecations


## Removed features
