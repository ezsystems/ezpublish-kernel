# Backwards compatibility changes

Changes affecting version compatibility with former or future versions.

## Changes

* `eZ\Publish\SPI\Search\Handler` has received changes, indexing related methods have been moved to separate interfaces:
  `eZ\Publish\SPI\Search\Indexer\FullTextIndexer` for handlers like legacy search engine that only need to index on content field changes.
  `eZ\Publish\SPI\Search\Indexer\FullContentDomainIndexer` for handlers like Solr and ElasticSearch which needs to update index on all content model changes _(Content and Locations)_.


## Deprecations


## Removed features
