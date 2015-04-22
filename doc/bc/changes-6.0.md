# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Internal FieldTypeService->buildFieldType() has been removed

  The internal function to get SPI version of FieldType has been removed and
  is now instead available on the internal FieldTypeRegistry.
  Corresponding API: FieldTypeService->getFieldType(), is unchanged.

* Search implementations have been refactored out of persistence into own namespace

    Implementations have been moved out of `eZ\Publish\Core\Persistence`
    namespace into their own namespace at `eZ\Publish\Core\Search`. With that, previously
    deprecated methods of the main storage handler (implementing
    `eZ\Publish\SPI\Persistence\Handler` interface) have been removed:

    * `searchHandler()`
    * `locationSearchHandler()`

    These are now available in the main search handler, implementing
    new `eZ\Publish\SPI\Search\Handler` interface, as:

    * `contentSearchHandler()` (replaces `searchHandler()`)
    * `locationSearchHandler()`

    Main search handler can now be retrieved from the service container through
    `ezpublish.spi.search` service identifier. This service may in future return
    different implementations of the interface, for example one providing caching
    or emitting signals for slots. At the moment it is aliased
    to the concrete implementation of the storage engine, available through
    `ezpublish.spi.search_engine` service identifier. This is in turn aliased
    to the Legacy Search Engine, available through `ezpublish.spi.search.legacy` service
    identifier.

    Legacy Search Engine is at the moment of writing the only officially supported Search Engine.

    With the implementation move, service tags for sort clause, criteria and
    criteria field value handlers have also changed. Previous service tags:

    * `ezpublish.persistence.legacy.search.gateway.criterion_handler.content`
    * `ezpublish.persistence.legacy.search.gateway.criterion_handler.location`
    * `ezpublish.persistence.legacy.search.gateway.criterion_field_value_handler`
    * `ezpublish.persistence.legacy.search.gateway.sort_clause_handler.content`
    * `ezpublish.persistence.legacy.search.gateway.sort_clause_handler.location`

    are now changed to the following, respectively:

    * `ezpublish.search.legacy.gateway.criterion_handler.content`
    * `ezpublish.search.legacy.gateway.criterion_handler.location`
    * `ezpublish.search.legacy.gateway.criterion_field_value_handler`
    * `ezpublish.search.legacy.gateway.sort_clause_handler.content`
    * `ezpublish.search.legacy.gateway.sort_clause_handler.location`

* Legacy Search Engine FullText searchThresholdValue -> stopWordThresholdFactor

    EZP-24213: the "Stop Word Threshold" configuration, `searchThresholdValue`, was hardcoded
    to 20 items. It is now changed to `stopWordThresholdFactor`, a factor (between 0 and 1)
    for the percentage of content objects to set the Stop Word Threshold to. Default value
    is set to 0.66, meaning if you search for a common word like "the", it will be ignored
    from the search expression if more then 66% of your content contains the word.

    Note: Does not affect future Solr/ElasticSearch search engines which has far more
          advanced search options built in.

* Semantic configuration for search engines has been implemented. At the moment of writing, only
  Legacy Search Engine is supported. Search engine bundles are also introduced here, these need to
  be activated in `EzPublishKernel.php` in order for the engine to be available for configuration.
  For the Legacy Search Engine the bundle is located at `eZ/Bundle/EzPublishLegacySearchEngineBundle`.

  With semantic configuration for search engines, repository configuration has changed. Previous
  structure:

  ```yml
  ezpublish:
      repositories:
          main:
              engine: legacy
              connection: default
  ```

  has been updated with search engine configuration. With it, storage settings are now moved under
  `storage` key. New structure looks like this:

  ```yml
  ezpublish:
      repositories:
          main:
              storage:
                  engine: legacy
                  connection: my_connection
              search:
                  engine: legacy
                  connection: my_connection
  ```

  The same as was previously the case with storage configuration, it is not mandatory to provide
  search configuration. In that case the system will try to use default search engine and default
  connection. Old structure is still supported, but is deprecated. The support will be removed in
  one of the future releases.

* `eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider` is has been renamed to
  `eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider`, as it now provides
  repository configuration for both storage and search engines. Class signature has remained the
  same.
  
* TextLine field type `StringLengthValidator` has changed. `false` isn't considered as valid value any more for
  `minStringLength` and `maxStringLength`. Use `null` instead of `false` if you want to deactivate these validators.
  Default validator value has been changed accordingly.
  
* `eZ\Publish\API\Repository\ContentTypeService::createContentType` can now accept a `ContentTypeCreateStruct` without
  any `FieldDefinitionCreateStruct`

* `eZ\Publish\Core\Repository\ContentService::deleteVersion()` now throws `BadStateException` when
  deleting last version of the Content. Since Content without a version does not make sense, in this
  case `eZ\Publish\Core\Repository\ContentService::deleteContent()` should be used instead.

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

## Changes from 2015.01 (6.0.0-alpha1)

* Bundle `EzPublishElasticsearchBundle` has been renamed to `EzPublishElasticsearchSearchEngineBundle`

* Bundle `EzPublishSolrBundle` has been renamed to `EzPublishSolrSearchEngineBundle`
