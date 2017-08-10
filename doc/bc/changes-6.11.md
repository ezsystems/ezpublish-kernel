# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

- `eZ\Publish\Core\REST\Common\Output\Generator\Json\Object` renamed to `JsonObject`

  For PHP 7.2 compatibility _(found when testing against beta2)_ we need to avoid uing the word `object` in class
  names which has now been added as a keyword in the language and made avaiable as type hint for objects.

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
