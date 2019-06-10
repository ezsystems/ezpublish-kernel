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

## Deprecated features

* Using SiteAccess-aware `pagelayout` setting is derecated, use `page_layout` instead.
* View parameter `pagelayout` set by `pagelayout` setting is deprecated, use `page_layout` instead in your Twig templates.