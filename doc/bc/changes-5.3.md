# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* Doctrine is now used instead of Zeta Database for legacy storage engine.
  **Semantic configuration for database settings has changed.**
  It is now mandatory to configure a Doctrine connection (see DoctrineBundle configuration), and a repository:

  ```yaml
  doctrine:
      dbal:
          default_connection:       default
          connections:
              default:
                  dbname:           Symfony2
                  user:             root
                  password:         null
                  host:             localhost
              my_connection:
                  dbname:           customer
                  user:             root
                  password:         null
                  host:             localhost

  ezpublish:
      repositories:
          main:
              # legacy => Legacy storage engine
              engine: legacy
              connection: my_connection
  ```

* New method `eZ\Publish\API\Repository\RoleService::deletePolicy` is introduced.

* Method `eZ\Publish\API\Repository\RoleService::removePolicy` will throw
  `eZ\Publish\API\Repository\Exceptions\InvalidArgumentException` in case when
  Policy does not belong to the given Role.

* User login is no longer done via legacy. It now uses native form_login.
  Legacy `user/login` and `user/logout` module views are deactivated when not in legacy mode.
  Authentication is forced in Symfony. **As such, legacy login/sso handlers won't work any more**.
  In `legacy_mode: true` (e.g. for admin interface), legacy user is still injected in the repository.

* Session name is now always prefixed by `eZSESSID`.

* `is_logged_in` cookie is not sent or used any more by Symfony stack (it is still used by legacy though).
  Anonymous state is now checked by the presence of a session cookie (prefixed by `eZSESSID`).

* **Lazy services**: `ezpublish.api.repository`, repository services (e.g. `ezpublish.api.service.content`),
  and a few others (e.g. `ezpublish.config.resolver`) are now [lazy services](http://symfony.com/doc/2.3/components/dependency_injection/lazy_services.html).
  You can now safely inject them, even in early request listeners. They will be booted only when necessary.

* **SignalSlot**: Slot factories are not needed any more as Slots are now directly attached to SignalDispatcher.
  Therefore `ContainerSlotFactory` has been removed.

* New search criterion `eZ\Publish\API\Repository\Values\Content\Query\Criterion\MapLocationDistance`
  is introduced.

* New search sort clause `eZ\Publish\API\Repository\Values\Content\Query\SortClause\MapLocationDistance`
  is introduced.

* Constructor signature of `eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver` has changed.
  SiteAccess is no longer injected in constructor, but with dedicated setter.
  This setter is defined in `eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware` interface, which `ConfigResolver` now implements.

* REST v2 POST /user/sessions
  For story EZP-22163 to allow for login with existing sessions+csrf token this endpoint has been slightly changed to 1. not return location header, but continue to return session info directly 2. Return 200 instead of 201 in this case if session and login matches (409 if not). See doc/specifications/rest/REST-API-V2.rst for latest info.

* Renamed fieldtypes IO Services:
  ezpublish.fieldType.ezbinaryfile.IOService => ezpublish.fieldType.ezbinaryfile.io_service
  ezpublish.fieldType.ezimage.IOService => ezpublish.fieldType.ezimage.io (ezpublish.fieldType.ezimage.io_service also existed, but wasn't the expected one)

* 5.3.4: `ViewCaching` legacy setting is now enforced and injected in legacy kernel when booted. This is to avoid persistence/Http
  cache clear not working when publishing content.

* 5.3.5: Search implementations have been refactored out of persistence into own namespace

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

* 5.3.5: Legacy Search Engine FullText searchThresholdValue -> stopWordThresholdFactor

    EZP-24213: the "Stop Word Threshold" configuration, `searchThresholdValue`, was hardcoded
    to 20 items. It is now changed to `stopWordThresholdFactor`, a factor (between 0 and 1)
    for the percentage of content objects to set the Stop Word Threshold to. Default value
    is set to 0.66, meaning if you search for a common word like "the", it will be ignored
    from the search expression if more then 66% of your content contains the word.

    Note: Does not affect future Solr/ElasticSearch search engines which has far more
          advanced search options built in.

* 5.3.6: Semantic configuration for search engines has been implemented

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

* 5.3.6: `eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider` is has been renamed to
  `eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider`, as it now provides
  repository configuration for both storage and search engines. Class signature has remained the
  same.

* 5.3.6: `eZ\Publish\Core\Repository\ContentService::deleteVersion()` now throws `BadStateException`
  when deleting last version of the Content. Since Content without a version does not make sense, in
  this case `eZ\Publish\Core\Repository\ContentService::deleteContent()` should be used instead.


* 5.3.8: $fieldFilters argument on Search service has been renamed to $languageFilter
  Reason is to better communicate what the argument is used for. No changes
  to it's structure is done so this is a purly cosmetic change.

## Deprecations

* Method `eZ\Publish\API\Repository\RoleService::removePolicy` is deprecated in
  favor of new method `eZ\Publish\API\Repository\RoleService::deletePolicy`.

* Method `eZ\Publish\API\Repository\UserService::loadAnonymousUser` is deprecated
  in favor of using `eZ\Publish\API\Repository\UserService::loadUser`, passing
  anonymous user ID as argument.

* Basic authentication for REST: In `security.yml, `ezpublish_http_basic` is deprecated in
  favor of standard `http_basic`.

* `ezpublish.api.repository.lazy` service is deprecated in favor of `ezpublish.api.repository`, which
  is now a lazy service.

* In semantic configuration, `ezpublish.system.<siteAccessName>.session_name` is deprecated.
  Use `ezpublish.system.<siteAccessName>.session.name` instead.

* `Regex\URI` and `Regex\Host` SiteAccess matchers are deprecated as reverse match is not possible with them (i.e. see `VersatileMatcher` interface).

* All Location based SortClauses, as well as PriorityCriterion and DepthCriterion has been
  deprecated for content search use since their behaviour is unpredictable by design when
  content has several locations. Instead use same functionality on new Location Search API.

* 5.3.3: Added $useAlwaysAvailable argument for all ContentService methods with languages filtering
  Default value will be changed from false to true in 5.4 as part of story ( https://jira.ez.no/browse/EZP-22191 ):
  "As a User I expect API's with language filters to respect Always available flag"

* As of eZ Publish 5.3.3/2014.11 UserGroup->subGroupCount is deprecated
  API\Repository\Values\User\UserGroup->subGroupCount will be removed in future version,
  this value can be obtained on demand using search service or other API methods.

No further changes are known in this release at the time of writing.
See online on your corresponding eZ Publish version for
updated list of known issues (missing features, breaks and errata).
