# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Internal FieldTypeService->buildFieldType() has been removed

  The internal function to get SPI version of FieldType has been removed and
  is now instead available on the internal FieldTypeRegistry.
  Corresponding API: FieldTypeService->getFieldType(), is unchanged.


## Deprecations

* `eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger::purge()` is deprecated and will be removed in v6.1.
  Use `eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger::purgeForContent()` instead.


## Removed features

* `getLegacyKernel()` shorthand method in `eZ\Bundle\EzPublishCoreBundle\Controller` has been removed.
  If you used it, please base your controller on `eZ\Bundle\EzPublishLegacyBundle\Controller` instead.
  
* `legacy_mode` setting has been removed.
  Move your setting to `ez_publish_legacy` (LegacyBundle) namespace instead:
  
  ```yml
  # This is deprecated
  ezpublish:
      system:
          my_siteaccess:
              legacy_mode: true
              
  # New setting
  ez_publish_legacy:
      system:
          my_siteaccess:
              legacy_mode: true
  ```
  
* `legacy_aware_routes` setting has been removed.
  Move your setting to `ez_publish_legacy` instead.

* Legacy has been moved to its own package
  Everything about legacy (`eZ/Publish/Core/MVC/Legacy` and `eZ/Bundle/EzPublishLegacyBundle` have been removed from
  ezpublish-kernel, and moved to their own package, `ezsystems/legacy-bridge`. The namespaces haven't been changed.
  If you rely on classes from those namespaces, please update your composer.json files to include this package as well.

* eZ Publish Legacy isn't included by default anymore
  The legacy-bridge requirement introduced in this version isn't included by default. The legacy application, as well
  as the related bundle, libraries and configuration, are no longer shipped by default.
