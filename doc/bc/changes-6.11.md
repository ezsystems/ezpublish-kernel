# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

## Deprecations

- EZP-26885: Field Type external storage
  - Abstract base class `eZ\Publish\Core\FieldType\StorageGateway` for the gateway of a Field Type
   external storage is deprecated.
  - The `eZ\Publish\Core\FieldType\StorageGateway::setConnection` method is deprecated.

  See [6.11 Upgrade Notes](../upgrade/6.11.md) for the details.

## Removed features
