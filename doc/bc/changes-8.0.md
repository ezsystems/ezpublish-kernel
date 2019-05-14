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
        

