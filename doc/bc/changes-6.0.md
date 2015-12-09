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

* `eZ\Publish\API\Repository\Values\Content\Query` and `eZ\Publish\API\Repository\Values\Content\LocationQuery`
  property `$limit` is now defined as `integer` (instead of `integer|null`), which means its value must always be
  set. By default, it's value will be `25`. No way is provided to return all search hits, pagination should be used
  if full result set is desired.

* `eZ\Publish\API\Repository\LocationService::loadLocationChildren()` signature has changed, default value of
  parameter `$limit` is now `25`. No way is provided to return all children, pagination should be used if full
  result set is desired.

* `eZ\Publish\API\Repository\UserService::loadUsersOfUserGroup()` signature has changed, default value of
  parameter `$limit` is now `25`. No way is provided to return all users, pagination should be used if full
  result set is desired.

* `eZ\Publish\API\Repository\UserService::loadSubUserGroups()` signature has changed, parameters `$offset = 0`
  and `$limit = 25` are added. No way is provided to return all user groups, pagination should be used if full
  result set is desired.

* `eZ\Publish\API\Repository\UserService::loadUserGroupsOfUser()` signature has changed, parameters `$offset = 0`
  and `$limit = 25` are added. No way is provided to return all user groups, pagination should be used if full
  result set is desired.

* SiteAccess service (`ezpublish.siteaccess`) is not synchronized any more.
  Synchronized services are deprecated as of Symfony 2.7.

* The Values for `BinaryFile` and `Media` FieldType now expose the content/download URL as the `url` property.
  Before, it contained the physical path to the file, e.g. `var/site/storage/original/...`. Since this path isn't
  allowed to pass through the rewrite rules for security, it was not usable.
  This also affects REST, that will now expose a valid HTTP download URL.
  
* `csrf_token` variable is not passed to login template any more. Use `csrf_token()` Twig function to generate it instead.
  ```jinja
  <input type="hidden" name="_csrf_token" value="{{ csrf_token("authenticate") }}" />
  ```

* Float field type `FloatValueValidator` has changed. `false` isn't considered as valid value any more for
  `minFloatValue` and `maxFloatValue`. Use `null` instead of `false` if you want to deactivate these validators.
  Default validator value has been changed accordingly.

* `eZ\Publish\API\Repository\Values\ValueObject\SearchHit` has a new property `$matchedTranslation`,
  which will hold language code of the Content translation that matched the search query.

* Role draft functionality has been added, which has led to some API changes:
  `eZ/Publish/API/Repository/RoleService::createRole` now returns a `eZ/Publish/API/Repository/Values/User/RoleDraft`.
  New methods have been added to `eZ/Publish/API/Repository/RoleService`: `createRoleDraft`, `loadRoleDraft`,
  `updateRoleDraft`, `addPolicyByRoleDraft`, `removePolicyByRoleDraft`, `deleteRoleDraft`, and `publishRoleDraft`.
  `eZ/Publish/API/Repository/Values/User/Role` now has a `status` property, which may be one of `Role::STATUS_DEFINED`
   or `Role::STATUS_DRAFT`.

* Signature of Repository `setCurrentUser()` & `hasAccess()` changed to accept `UserReference`
  As part of EZP-24834, a new API interface `UserReference` has been introduced that only
  holds the user id, and can be used to specify current user and avoid having to load the
  whole User object. User API abstract object has been changed to implement this so there is no BC
  break for API use, only for custom API implementations.
  Also new `getCurrentUserReference()` method has been added on Repository to get this object.
  
* Internal `limitationMap` repository service setting (for `RoleService`) has been renamed to `policyMap`.

## Deprecations

* `eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger::purge()` is deprecated and will be removed in v6.1.
  Use `eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger::purgeForContent()` instead.

* The REST resource `/content/views` is deprecated and will be removed in v6.1.
  `/views`` replaces it.
  Until it is removed, POST to `/content/views` will return a 301 instead of a 200, and include location header to the
  new resource.

* Some methods have been deprecated in `eZ/Publish/API/Repository/RoleService`: `updateRole`, `addPolicy`,
  `removePolicy`, `deletePolicy`, and `updatePolicy`. Use the corresponding `*Draft` and `*ByRoleDraft` methods instead.

* The `viewLocation`, `embedLocation`, `viewContent` and `embedContent` Content\ViewController`` actions are deprecated.
  `viewAction` and `embedAction` must be used instead. Both accept the location as an extra parameter.
  The corresponding `location_view` configuration is also deprecated. It will transparently be converted to `content_view`,
  but you should update your configuration:
  ```
  location_view:
    full:
      article:
        match:
          Identifier\ContentType: [article]
  ```
  becomes:
  ```
  content_view:
    full:
      article:
        match:
          Identifier\ContentType: [article]
  ```

  Rules that use a custom location view controller can't be transparently changed.
  Those need to be changed to custom content view controllers, that use a contentId instead of a locationId as an
  argument. The location is available in the `$parameters` array.

* The `viewBlock` and `viewBlockById` `PageController` actions are deprecated.
  `viewAction` must be used instead. It accepts both `block` and `id` as parameters.

* `eZ\Bundle\EzPublishCoreBundle\Controller\PageController` is deprecated and will be removed once
  deprecated methods from base controller are also removed. You must use the base `PageController`
  and its `viewAction` method.

* The `eZ\Publish\Core\MVC\Symfony\View\MatcherInterface` interface is deprecated.
  Matchers that use it will stop working until they implement `eZ\Publish\Core\MVC\Symfony\View\ViewMatcherInterface`
  instead. This interface exposes a single `match()` method that expects an `eZ\Publish\Core\MVC\Symfony\View\View`
  as its argument. Implementations should check the type of value the View contains, depending on what it is matching
  against (`LocationValueView`, `ContentValueView`, `BlockValueView`.

* The AbstractMatcherFactory, as well as the classes inheriting from it, are deprecated.
  `ServiceAwareMatcherFactory`, or its parent `ClassNameMatcherFactory` can be used instead.
  The match configuration can be  provided using dynamic settings in the services definitions, and a relative namespace
  can be set using the constructor.

* API Location::SORT_FIELD_MODIFIED_SUBNODE deprecated

  This feature is not exposed in eZ Platform in anyway, and this remaining constant will
  be removed in a future version together with the need to keep this column updated on tree
  operations, which caused deadlocks in legacy.

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

* Following criteria and sort clauses deprecated in 5.3 are removed:

    * `eZ\Publish\API\Repository\Values\Content\Query\Criterion\Depth`
    * `eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationPriority`
    * `eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationDepth`
    * `eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPath`
    * `eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPathString`
    * `eZ\Publish\API\Repository\Values\Content\Query\SortClause\LocationPriority`

* Deprecated virtual property `$criterion` on class `eZ\Publish\API\Repository\Values\Content\Query`,
  is removed.

* Removed support for XML DOMDocument values on `eZ\Publish\SPI\Persistence\Content\FieldValue`

  As part of EZP-24832, `$data` property now needs to be scalar/array type, so it can be json
  serialized in future storage engine improvements. Likewise `$externalData`, like any other
  `eZ\Publish\SPI\Persistence\ValueObject` property, has been documented to have to be
  serializable, as it is cached by Persistence Cache which depends on this.


## Changes from 2015.01 (6.0.0-alpha1)

* Bundle `EzPublishElasticsearchBundle` has been renamed to `EzPublishElasticsearchSearchEngineBundle`

* Bundle `EzPublishSolrBundle` has been renamed to `EzPublishSolrSearchEngineBundle`
