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

- EZP-27497: Large container class file when there are many siteaccesses

  What changed: `eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Contextualizer` was changed to omit generating compiled config to Symfony if a given group and scope is empty, severely reducing the size of the generated Symfony container. This can in some cases affect custom parsers that rely on this value being set when it is now not.

## Removed features
