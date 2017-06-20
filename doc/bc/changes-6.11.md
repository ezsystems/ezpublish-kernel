# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

## Deprecations

- EZP-26885: Field Type external storage
  - Abstract base class `eZ\Publish\Core\FieldType\StorageGateway` for the gateway of a Field Type
    external storage and specifically its method `setConnection` are deprecated.
  - Abstract base class `eZ\Publish\Core\FieldType\GatewayBasedStorage` for Field Type external storage
    which uses gateway and specifically its method `getGateway` are deprecated.

  See [6.11 Upgrade Notes](../upgrade/6.11.md) for the details.

## Removed features
