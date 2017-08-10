# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

- 6.7.8: `eZ\Publish\Core\REST\Common\Output\Generator\Json\Object` renamed to `JsonObject`

  For PHP 7.2 compatibility we need to avoid using the word `object` in class
  names which has now been added as a keyword in the language and made available as type hint for objects.

## Deprecations

## Removed features
