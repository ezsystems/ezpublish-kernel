# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Removed features

* Elasticsearch support has been dropped. It supported Elasticsearch 1.x,
  while the latest Elasticsearch release is 7.0.

  The support for this search engine will be provided once again as a separate bundle.
  
* The following Field Types are not supported any more and have been removed:
    * ezprice 
    
* Deprecated method `getName` from the interface `eZ\Publish\SPI\FieldType\FieldType` has been removed. 
  All implementations of this method are also removed. If you used it, please use `eZ\Publish\SPI\FieldType\Nameable` interface instead.
