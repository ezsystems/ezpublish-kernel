# Elasticsearch Storage Engine implementation

This document provides overview of the implementation of the Elasticsearch Storage Engine.

Elasticsearch Storage Engine implements SPI Content and Location search handler interfaces.
The rest of the interfaces are used from Legacy Storage Engine implementation. This is the same
as Solr storage engine is implemented, and will change with extracting SPI Search API out of
Persistence into its own SPI namespace. That will enable usage of Persistence Cache layer and
re-usage of common implementation, like field map and indexing slots.

## Motivation

Implementing:

1. Indexing, analyzing, sorting on and querying Field data
2. Location search

## Data structure

Content and Location objects are indexed as different document types. This was necessary in order
to support Location Search, since it can not be fully supported by denormalizing Location data into
a Content document.

Possible future implementation of direct search for nested documents would make indexing Content
and Locations unnecessary. Though this seems to be technically possible, it is not planned at the
moment (see discussion on https://github.com/elasticsearch/elasticsearch/issues/3022 for more info).

### Content document

Content documents contain their Locations and fields (per translation) as nested documents.
In Elasticsearch implementation of Lucene nested documents these do not exist outside of the parent
document context. This means it is not possible to independently search for them and return them as a
result of the search.

For the outline of the nested document feature see: http://www.slideshare.net/MarkHarwood/proposal-for-nested-document-support-in-lucene

Locations as nested documents of Content enable future implementation of e.g. LocationQuery
criterion that would combine Location conditions, which would make queries like "find all Content with
visible Location in this subtree" possible.

Fields as nested documents enable targeting only specific translations in search and with that also easier
implementation of language analysis per translation.

### Location document

Since Location always has exactly one Content, it's document does not contain nested documents.
Instead, Content data is denormalized into a Location document. Fields data is not indexed as a part
of Location document, doing so would have some drawbacks:

* Index size would significantly increase as the same Field data would have to be indexed once per
Content and additionally once for each of its Locations
* Multiple Locations of a Content would mess up scoring as the same Field data would be indexed
for each Location of a single Content

For these reasons Field and FullText criterion support with Location Search is not implemented here
and is still a subject for discussion. There are no technical problems what would prevent implementation
though, this can actually be done quite easily by reusing what is already implemented for Content Search.

## Implementation

Elasticsearch cluster is communicated with over its REST API. For this simple HTTP client is
used (it is actually copied from Solr Storage engine implementation).

### Gateway

Same Gateway implementation (with different dependencies) is used by both Content and Location search
handlers.

### Mapping (schema)

Mapping for Content and Location documents can be found in `Persistence/Elasticsearch/Resources/mappings`.
Dynamic mapping is available only for nested Field documents, otherwise types of fields are known and
are mapped statically.

### Mapper

Mapper service is used to create a Document object for a Content or Location. Mapping in a dedicated
service enables alternative implementations, for example a custom user implementation could index
Content fields as a part of a Location. This would of course need to be accompanied by a custom mapping
and implementation of Field and FullText criterion visitors for Location Search.

Other custom implementation could skip indexing Content if Content Search is not needed and so on.

### Document object

Document represents a document to be indexed by Elasticsearch index engine. It is described by:

1. id (Content or Location id)
2. type (Content or Location)
3. fields (document's fields, can contain other nested Documents in form of SPI DocumentField)

### Serializer

Serializer is a simple service used to convert a Document to a JSON string that can be passed over
Elasticsearch REST API.

### Criterion visiting

Elasticsearch Query DSL actually describes abstract syntax tree of queries/filters, which is very similar
to the structure of our criteria. Criterion visitors return simple hash structures that appropriately
describe parts of this AST. End result of criterion visiting can be JSON encoded to produce valid
Elasticsearch query that can be passed over the REST API.

#### Query and filter context

Query DSL uses different syntax for queries and filters, but often queries and filters are similar and
even the same. To cover for this, same criterion visitors are dispatched with query/filter context.

Elasticsearch aggregations (facets in Solr speak) work only in query context. For this reason filters
are applied as "filter" part of main "filtered" query.

### Extractor

Extractor is used by search handlers to extract search result from the data returned from Elasticsearch index.
Currently only `Loading` extractor is implemented, which returns search hits (Location and ContentInfo) by
loading them using the storage implementation. Alternative implementation could do the same by
reconstructing them from the returned Elasticsearch data.

### Language analysis

Analyzers can be configured per language code and are always named `analyzer_<language code>`, where hyphen in
language code is replaced by an underline. For example: `analyzer_cro_HR`, `analyzer_eng_GB`. Elasticsearch's
analyzer aliasing feature allows configuring one and the same analyzer for multiple language codes.

Only a mechanism to configure analysis per language is given. Provided analyzer configuration can only be
considered as a starting point -- this is Elasticsearch's domain and user is expected to configure analysis
as needed.
