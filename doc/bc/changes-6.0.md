# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Internal FieldTypeService->buildFieldType() has been removed

  The internal function to get SPI version of FieldType has been removed and
  is now instead available on the internal FieldTypeRegistry.
  Corresponding API: FieldTypeService->getFieldType(), is unchanged.


## Deprecations


## Removed features
