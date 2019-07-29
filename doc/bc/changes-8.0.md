# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Removed features

* Elasticsearch support has been dropped. It supported Elasticsearch 1.x,
  while the latest Elasticsearch release is 7.0.

  The support for this search engine will be provided once again as a separate bundle.

* The following Field Types are not supported any more and have been removed:
    * `ezprice`
    * `ezpage` together with block rendering subsystem

* The following configuration nodes are not available anymore:
    * `ezpublish.<scope>.ezpage.*`
    * `ezpublish.<scope>.block_view.*`

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

* Deprecated `eZ\Publish\SPI\FieldType\EventListener` interface, `eZ\Publish\SPI\FieldType\Event` class and
  `eZ\Publish\SPI\FieldType\Events` namespace have been dropped.

* Deprecated `eZ\Publish\Core\MVC\Symfony\Matcher\MatcherInterface` interface has been dropped. Deprecated classes relying on that interface have been removed as well:
    * `eZ\Publish\Core\MVC\Symfony\Matcher\AbstractMatcherFactory`
    * `eZ\Publish\Core\MVC\Symfony\Matcher\ContentBasedMatcherFactory`
    * `eZ\Publish\Core\MVC\Symfony\Matcher\ContentMatcherFactory`
    * `eZ\Publish\Core\MVC\Symfony\Matcher\LocationMatcherFactory`

* Deprecated Symfony framework templating component integration has been dropped.

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

## Removed services

* `ezpublish.field_type_collection.factory` has been removed in favor of `eZ\Publish\Core\FieldType\FieldTypeRegistry`

* `ezpublish.persistence.external_storage_registry.factory`

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
