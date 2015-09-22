# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Stash update brings a slight change to the configuration format.
  Instead of referring to `handlers`, it is now using the term `drivers`.

  ```diff
  stash:
      caches:
          default:
  -           handlers:
  +           drivers:
                  # When using multiple webservers, you must use Memcache or Redis
                  - FileSystem
              inMemory: true
          registerDoctrineAdapter: false
          # On Windows, using FileSystem, to avoid hitting filesystem limitations
          # you need to change the keyHashFunction used to generate cache directories to "crc32"
          # FileSystem
          #    keyHashFunction: crc32
  ```

* Property `id` for `eZ\Publish\Core\FieldType\Image\Value` (value object for `ezimage` FieldType) has changed.
  It wrongly contained the full image path, including the storage directory (e.g. `var/ezdemo_site/storage/images/`.
  To get the full path, use `uri` property.
  Ref: [EZP-23349](https://jira.ez.no/browse/EZP-23349)
  
* `border` image filter configuration has changed. It now takes a 3rd parameter for color (`#000` by default).
  This parameter accepts RGB hexa values. 1st is still border thickness for horizontal, 2nd thickness for vertical.
  
  ```yaml
  ezpublish:
      system:
          my_siteaccess:
              image_variations:
                  my_border_variation:
                      reference: null
                      filters:
                          # Adding a white border, 10px thick.
                          - { name: border, params: [10, 10, "#fff"] }
  ```
  
* `resize` image filter configuration has changed. It now follows the configuration provided in LiipImagineBundle:

  ```yaml
  ezpublish:
      system:
          my_siteaccess:
              image_variations:
                  my_border_variation:
                      reference: null
                      filters:
                          - { name: resize, params: {size: [1080, 1024]} }
  ```

* Language filtering has been changed to respect always available flag, this affects:
  - Criterion\LanguageCode second argument `$matchAlwaysAvailable` changed to true by default
  - ContentService methods with `$useAlwaysAvailable` has been changed to be true by default, affects:
    - loadContentByContentInfo
    - loadContentByVersionInfo
    - loadContent
    - loadContentByRemoteId

  If you have code that expects exception(load) or no content (search) if not a specific language
  exists, you should then review your code and consider setting these properties to false.
  
* HTTP cache is now always purged using a single HTTP request, being emulated (`local`) or real, always using `X-Location-Id` header.
  `X-Group-Location-Id` is not used any more and is thus deprecated.
  
  As a result, semantic setting `ezpublish.http_cache.purge_type` now only accepts `local` or `http` as values.
  `multiple_http` and `single_http` are deprecated and are now considered as `http`. You may change them to use `http`:
  
  ```diff
    ezpublish:
        http_cache:
    -        purge_type: multiple_http
    +        purge_type: http
    ```
* The usual `IOService` is overridden by a `TolerantIOService`. It replaces the previous `try/catch` blocks in
  binary FieldTypes external storage. When one of the IO layers returns a not found, a MissingBinaryFile will be
  returned, with fake properties: the requested id, no uri, no file size... if logging is enabled, an info message
  will still be logged.

* `ViewCaching` legacy setting is now enforced and injected in legacy kernel when booted. This is to avoid persistence/Http
  cache clear not working when publishing content.

* 5.4.2: Search implementations have been refactored out of persistence into own namespace

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

* 5.4.2: Legacy Search Engine FullText searchThresholdValue -> stopWordThresholdFactor

    EZP-24213: the "Stop Word Threshold" configuration, `searchThresholdValue`, was hardcoded
    to 20 items. It is now changed to `stopWordThresholdFactor`, a factor (between 0 and 1)
    for the percentage of content objects to set the Stop Word Threshold to. Default value
    is set to 0.66, meaning if you search for a common word like "the", it will be ignored
    from the search expression if more then 66% of your content contains the word.

    Note: Does not affect future Solr/ElasticSearch search engines which has far more
          advanced search options built in.

* 5.4.3: Semantic configuration for search engines has been implemented

    At the moment of writing, only Legacy Search Engine is supported. Search engine bundles are also
    introduced here, these need to be activated in `EzPublishKernel.php` in order for the engine to be
    available for configuration. For the Legacy Search Engine the bundle is located at
    `eZ/Bundle/EzPublishLegacySearchEngineBundle`.

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

* 5.4.3: `eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider` is has been renamed to
  `eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider`, as it now provides
  repository configuration for both storage and search engines. Class signature has remained the
  same.

* 5.4.3: `eZ\Publish\Core\Repository\ContentService::deleteVersion()` now throws `BadStateException`
  when deleting last version of the Content. Since Content without a version does not make sense, in
  this case `eZ\Publish\Core\Repository\ContentService::deleteContent()` should be used instead.

* 5.4.5: $fieldFilters argument on Search service has been renamed to $languageFilter
  Reason is to better communicate what the argument is used for. No changes
  to it's structure is done so this is a purly cosmetic change.

* 5.4.5: $languageFilter specification has changed to work as priority list of languages
  To be able to sort on translated values in a predictable way we need to deal with
  one language per content object at a time. This change only affects how the sort is
  performed internally, and not which fields are returned in the case of findContent.
  This furthermore better matches the behaviour of language lists for SiteAccess.
  Behaviour of Search engines will be gradually changed to reflect this change, Solr first.
  Note: Possibility to search across all languages remains, however Field SortClause will
  for instance not work properly in this case, and we plan to start to give warnings about this.

* 5.4.5: `eZ\Publish\API\Repository\Values\ValueObject\SearchHit` has a new property `$matchedTranslation`,
  which will hold language code of the Content translation that matched the search query.

* 5.4.5: Signature of Repository `setCurrentUser()` & `hasAccess()` changed to accept `UserReference`
  As part of EZP-24834, a new API interface `UserReference` has been introduced that only
  holds the user id, and can be used to specify current user and avoid having to load the
  whole User object. User API abstract object has been changed to implement this so there is no BC
  break for API use, only for custom API implementations.

## Deprecations

* `imagemagick` siteaccess settings are now deprecated. It is mandatory to remove them.
  An exception will be thrown when compiling the container to remind to remove them

  ```diff
  ezpublish:
      system:
          my_siteaccess:
  -            imagemagick:
  -                pre_parameters:
  -                post_parameters:
  ```

* `imagemagick` settings at the application level (`convert` path and filters definitions) have been deprecated.
  They will be removed in v6.0.

* `mimeType` property from BinaryFile is deprecated. The value might not be present for some IO handlers.
  Use `getMimeType` from the IOService instead.

* `IOService::getExternalPath()` and `IOService::getInternalPath()` have been removed.
  `getInternalPath()`, that returned the handler level path, can be obtained using the uri property.

  and by getting the URI from the returned value object.
  `getExternalPath()`, that returns the id seen from the IOService, can be replaced by `IOService::loadBinaryFileByUri()`.
  
* Semantic setting `ezpublish.http_cache.timeout` has been deprecated and is no longer used. It can be safely removed.

  ```diff
  ezpublish:
      http_cache:
  -        timeout: 1
  ```
  
* *Identity definer* services (using `ezpublish.identity_definer` tag) are deprecated in favor of 
  [custom Context Providers from `FOSHttpCacheBundle`](http://foshttpcachebundle.readthedocs.org/en/latest/reference/configuration/user-context.html#custom-context-providers).
  
  `ezpublish.identity_definer` service tag and related classes/interfaces will be removed in v6.0
  
* 5.4.2: `eZ\Bundle\EzPublishCoreBundle\Controller::getLegacyKernel()` is deprecated (will be removed in v6.0).
  Use `eZ\Bundle\EzPublishLegacyBundle\Controller::getLegacyKernel()` instead. 
  
* 5.4.2: `legacy_mode` setting is deprecated and will be removed in v6.0.
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

* 5.4.2: `legacy_aware_routes` setting is deprecated and will be removed in v6.0.
  Move your setting to `ez_publish_legacy` instead.
  
No further changes are known in this release at the time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).


## Removed features

* Following image filters are not supported any more and have been removed:
  * `flatten`. Obsolete, images are automatically flattened.
  * `bordercolor`. Not applicable any more.
  * `border/width`. Not applicable any more.
  * `colorspace/transparent`
  * `colorspace`

* `IOService::getMetadata()` has been removed
  ImageSize metadata is now handled at fieldtype level, in the Legacy
  conversion, and upon upload on the local file.
