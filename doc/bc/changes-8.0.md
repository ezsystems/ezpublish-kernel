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

## Deprecated features

* Using SiteAccess-aware `pagelayout` setting is derecated, use `page_layout` instead.
* View parameter `pagelayout` set by `pagelayout` setting is deprecated, use `page_layout` instead in your Twig templates.


## Renamed features

* `ezpublish` global twig variable was renamed to `ezplatform`
* `ez_is_field_empty` twig function  was renamed to `ez_field_is_empty`
* `ez_first_filled_image_field_identifier` twig function  was renamed to `ez_content_field_identifier_first_filed_image`
* `ez_render_fielddefinition_settings` twig function  was renamed to `ez_render_field_definition_settings`
* `ez_image_asset_content_field_identifier` twig function  was renamed to `ez_content_field_identifier_image_asset`
* `richtext_to_html5` twig filter  was renamed to `ez_richtext_to_html5`
* `richtext_to_html5_edit` twig filter  was renamed to `ez_richtext_to_html5_edit`
