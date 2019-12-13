# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Removed features

* Elasticsearch support has been dropped. It supported Elasticsearch 1.x,
  while the latest Elasticsearch release is 7.0.

  The support for this search engine will be provided once again as a separate bundle.

* The following Field Types are not supported any more and have been removed:
    * `ezprice`,
    * `ezpage` together with block rendering subsystem,
    * `ezsrrating`.

* The following configuration nodes are not available anymore:
    * `ezpublish.<scope>.ezpage.*`
    * `ezpublish.<scope>.block_view.*`
    * `ezpublish.siteaccess.relation_map` is replaced by `getSiteAccessesRelation` method from `eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService` 
    
* REST Client has been dropped.

* REST Server implementation and Common namespace have been removed in favor of
  eZ Platform REST Bundle available via
  [ezsystems/ezplatform-rest](https://github.com/ezsystems/ezplatform-rest) package.

* Assetic support has been dropped.

* Minimal PHP version has been raised to 7.3.

* Deprecated method `getName` from the interface `eZ\Publish\SPI\FieldType\FieldType` has been changed.
  Now it accepts two additional parameters: `FieldDefinition $fieldDefinition` and `string $languageCode`

* Interface `eZ\Publish\SPI\FieldType\FieldType` has been transformed to abstract class.

* Interface `eZ\Publish\SPI\FieldType\Nameable` has been removed.

* `ez_trans_prop` twig function was removed

* `ezrichtext` Field Type has been completely removed from this package.
  Use [eZ Platform RichText Bundle](https://github.com/ezsystems/ezplatform-richtext) instead.

  It also implies that:
  * the semantic configuration available as:
      ```yaml
      ezpublish:
          ezrichtext:
      ```
    is no longer supported. To upgrade please make `ezrichtext` the top node:
      ```yaml
      ezrichtext:
      ```
  * the namespace `eZ\Publish\Core\FieldType\RichText` has been dropped (all classes are available
  in the mentioned package).

* Deprecated hash types constants have been dropped from `\eZ\Publish\API\Repository\Values\User\User`.

* Deprecated `eZ\Publish\SPI\FieldType\EventListener` interface, `eZ\Publish\SPI\FieldType\Event` class and
  `eZ\Publish\SPI\FieldType\Events` namespace have been dropped.

* Deprecated `eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface` interface has been dropped. Deprecated classes relying on that interface have been removed as well:
    * `eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory`
    * `eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory`
    * `eZ\Publish\Core\MVC\Symfony\Matcher\ContentMatcherFactory`
    * `eZ\Publish\Core\MVC\Symfony\Matcher\LocationMatcherFactory`

* Deprecated Symfony framework templating component integration has been dropped.

* Following methods have been removed:

    * `\eZ\Publish\API\Repository\UserService::loadAnonymousUser`
    * `\eZ\Publish\API\Repository\Repository::getCurrentUser`
    * `\eZ\Publish\API\Repository\Repository::getCurrentUserReference`
    * `\eZ\Publish\API\Repository\Repository::setCurrentUser`
    * `\eZ\Publish\API\Repository\Repository::hasAccess`
    * `\eZ\Publish\API\Repository\Repository::canUser`
    * `\eZ\Publish\API\Repository\RoleService::updateRole`
    * `\eZ\Publish\API\Repository\RoleService::addPolicy`
    * `\eZ\Publish\API\Repository\RoleService::deletePolicy`
    * `\eZ\Publish\API\Repository\RoleService::updatePolicy`
    * `\eZ\Publish\API\Repository\RoleService::loadPoliciesByUserId`
    * `\eZ\Publish\API\Repository\RoleService::unassignRoleFromUser`
    * `\eZ\Publish\API\Repository\RoleService::unassignRoleFromUserGroup`

* The "Setup" folder and Section have been removed from the initial (clean installation) data.

* The "Design" Section has been removed from the initial (clean installation) data.

* The following obsolete tables have been removed from the database schema:
    - ezapprove_items,
    - ezbasket,
    - ezcollab_group,
    - ezcollab_item,
    - ezcollab_item_group_link,
    - ezcollab_item_message_link,
    - ezcollab_item_participant_link,
    - ezcollab_item_status,
    - ezcollab_notification_rule,
    - ezcollab_profile,
    - ezcollab_simple_message,
    - ezcomment,
    - ezcomment_notification,
    - ezcomment_subscriber,
    - ezcomment_subscription,
    - ezcontentbrowserecent,
    - ezcurrencydata,
    - ezdiscountrule,
    - ezdiscountsubrule,
    - ezdiscountsubrule_value,
    - ezenumobjectvalue,
    - ezenumvalue,
    - ezforgot_password,
    - ezgeneral_digest_user_settings,
    - ezinfocollection,
    - ezinfocollection_attribute,
    - ezisbn_group,
    - ezisbn_group_range,
    - ezisbn_registrant_range,
    - ezm_block,
    - ezm_pool,
    - ezmessage,
    - ezmodule_run,
    - ezmultipricedata,
    - eznotificationcollection,
    - eznotificationcollection_item,
    - eznotificationevent,
    - ezoperation_memento,
    - ezorder,
    - ezorder_item,
    - ezorder_nr_incr,
    - ezorder_status,
    - ezorder_status_history,
    - ezpaymentobject,
    - ezpdf_export,
    - ezpending_actions,
    - ezprest_authcode,
    - ezprest_authorized_clients,
    - ezprest_clients,
    - ezprest_token,
    - ezproductcategory,
    - ezproductcollection,
    - ezproductcollection_item,
    - ezproductcollection_item_opt,
    - ezpublishingqueueprocesses,
    - ezrss_export,
    - ezrss_export_item,
    - ezrss_import,
    - ezscheduled_script,
    - ezsearch_search_phrase,
    - ezsession,
    - ezstarrating,
    - ezstarrating_data,
    - ezsubtree_notification_rule,
    - eztipafriend_counter,
    - eztipafriend_request,
    - eztrigger,
    - ezuservisit,
    - ezuser_discountrule,
    - ezvatrule,
    - ezvatrule_product_category,
    - ezvattype,
    - ezview_counter,
    - ezwaituntildatevalue,
    - ezwishlist,
    - ezworkflow,
    - ezworkflow_assign,
    - ezworkflow_event,
    - ezworkflow_group,
    - ezworkflow_group_link,
    - ezworkflow_process.

    If your project doesn't use them, you can drop them from your database by executing the SQL:
    ```sql
        DROP TABLE <table_name>;
    ```

* Clean Installer (`EzSystems\PlatformInstallerBundle\Installer\CleanInstaller`), its service
  definition (`ezplatform.installer.clean_installer`), and the
  `ezplatform.installer.clean_installer.class` parameter have been dropped. Instead use
  the `EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvents::BUILD_SCHEMA` event
  with an event subscriber, and if needed, additionally with the Core Installer
  (`EzSystems\PlatformInstallerBundle\Installer\CoreInstaller`).
  See eZ Platform Documentation for more details.

* The `ezplatform.installer.db_based_installer.class` and `ezplatform.installer.install_command.class`
  parameters have been dropped. The `ezplatform.installer.db_based_installer` service definition has
  been dropped as well. Instead, the FQCN-named service
  `EzSystems\PlatformInstallerBundle\Installer\DbBasedInstaller` is available.

* The `EzSystems\PlatformInstallerBundle\Command\InstallPlatformCommand` has been marked as final.
  Overriding it was never supported. Use the SchemaBuilder event-oriented extension point to inject
  custom behavior into the installation process (see eZ Platform Documentation for more details).

* The deprecated Legacy SQL schema files (`data/mysql/schema.sql`, `data/mysql/dfs_schema.sql`)
  have been removed. Use Schema Builder instead.

* The obsolete `data/demo_data.php` file has been removed.

* The following deprecated (since v6.11) LegacyStorage Gateways were removed:
    - `eZ\Publish\Core\FieldType\BinaryFile\BinaryBaseStorage\Gateway\LegacyStorage`,
    - `eZ\Publish\Core\FieldType\BinaryFile\BinaryFileStorage\Gateway\LegacyStorage`,
    - `eZ\Publish\Core\FieldType\MapLocation\MapLocationStorage\Gateway\LegacyStorage`,
    - `eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway\LegacyStorage`,
    - `eZ\Publish\Core\FieldType\Keyword\KeywordStorage\Gateway\LegacyStorage`,
    - `eZ\Publish\Core\FieldType\Media\MediaStorage\Gateway\LegacyStorage`,
    - `eZ\Publish\Core\FieldType\Url\UrlStorage\Gateway\LegacyStorage`,
    - `eZ\Publish\Core\FieldType\User\UserStorage\Gateway\LegacyStorage`.

  Use `DoctrineStorage` Gateways from the same namespace instead.

* Query Types: Traversing bundles to automatically register a Query Type by the naming
  convention `<Bundle>\QueryType\*QueryType` has been dropped.
  Register your Query Type as a service and explicitly tag that service with `ezpublish.query`
  or enable its automatic configuration (`autoconfigure: true`).

* The deprecated Symfony Dependency Injection Container parameters ending with `.class` have been
  removed, services relying on them have now their classes defined explicitly.
  To properly decorate a Symfony service, use the `decorates` attribute instead.
  For the full list of the dropped parameters please see the
  [8.0/dropped-container-parameters.md](8.0/dropped-container-parameters.md) document.

## Deprecated features

* Using SiteAccess-aware `pagelayout` setting is derecated, use `page_layout` instead.
* View parameter `pagelayout` set by `pagelayout` setting is deprecated, use `page_layout` instead in your Twig templates.


## Renamed features

* `ezpublish` global twig variable was renamed to `ezplatform`
* `ez_is_field_empty` twig function  was renamed to `ez_field_is_empty`
* `ez_first_filled_image_field_identifier` twig function  was renamed to `ez_content_field_identifier_first_filled_image`
* `ez_render_fielddefinition_settings` twig function  was renamed to `ez_render_field_definition_settings`
* `ez_image_asset_content_field_identifier` twig function  was renamed to `ez_content_field_identifier_image_asset`
* `richtext_to_html5` twig filter  was renamed to `ez_richtext_to_html5`
* `richtext_to_html5_edit` twig filter  was renamed to `ez_richtext_to_html5_edit`
* `\eZ\Bundle\EzPublishIOBundle\ApiLoader\HandlerFactory` class was renamed to `HandlerRegistry`
* `ezpublish.core.io.metadata_handler.factory` service was renamed to `ezpublish.core.io.metadata_handler.registry`
* `ezpublish.core.io.binarydata_handler.factory` service was renamed to `ezpublish.core.io.binarydata_handler.registry`

## Changed features

* The signature of the `\eZ\Publish\API\Repository\SearchService::supports` method was changed to:
  ```php
  public function supports(int $capabilityFlag): bool;
  ```
* The signature of the `\eZ\Publish\SPI\Search\Capable::supports` method was changed to:
  ```php
  public function supports(int $capabilityFlag): bool;
  ```
* The signature of the `\eZ\Publish\API\Repository\Values\ValueObject\SiteAccess::__construct` method was changed to make `name` property required:
  ```php
  public function __construct(
      string $name,
      string $matchingType = self::DEFAULT_MATCHING_TYPE,
      $matcher = null,
      ?string $provider = null
  );
  ```

* The signature of the `\eZ\Publish\API\Repository\Values\ContentType\ContentType::getFieldDefinitions` method was changed to:
  ```php
  abstract public function getFieldDefinitions(): FieldDefinitionCollection;
  ```

## Removed services

* `ezpublish.field_type_collection.factory` has been removed in favor of `eZ\Publish\Core\FieldType\FieldTypeRegistry`

* `ezpublish.persistence.external_storage_registry.factory`

* `ezpublish.config.resolver.core` has been removed. `eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver` should be used instead

## Changed behavior

* Service based View Matchers now require to be tagged with `ezplatform.view.matcher`. Moreover now to use it you have to prefix service name with `@` sign:
```yaml
site:
    content_view:
        full:
            home:
                template: "content.html.twig"
                match:
                    '@App\Matcher\MyMatcher': ~
```

* Service based SiteAccess Matchers now require to be tagged with `ezplatform.siteaccess.matcher`.

* `eZ\Bundle\EzPublishCoreBundle\Controller` extends `Symfony\Bundle\FrameworkBundle\Controller\AbstractController` instead of `Symfony\Bundle\FrameworkBundle\Controller\Controller` which has limited access to the dependency injection container. See https://symfony.com/doc/current/service_container/service_subscribers_locators.html

* SiteAccessAware Repository layer is now used by default. If you need to load repository object in all translations, explicitly pass `\eZ\Publish\API\Repository\Values\Content\Language::ALL` as as prioritized languages list.
