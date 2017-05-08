Facets
======

Analysis
--------

The basic idea when working with facets in the eZ Publish API is the
following:

### Building the Initial Search

When executing a search you also specify the [`FacetBuilder`](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/FacetBuilder.php) for all facets
you want the search engine to build. Those might be many:

    $query->facetBuilders = [
        new FacetBuilder\ContentTypeFacetBuilder([
            'name' => 'Type of Document',
        ]),
        new FacetBuilder\UserFacetBuilder([
            'name' => 'Owner of Document',
            'type' => FacetBuilder\UserFacetBuilder::OWNER,
        ]),
    ];

The returning result will contain results for the specified facets, if
the used search engine does support facets. If facets are not supported
an empty array should be returned:

    $result = new SearchResult([
        'facets' => new ContentTypeFacet([
            'name' => 'Type of Document', // Copied from facet builder
            'entries' => [
                '$contentTypeIdentifier' => '$count',
                // …
            ],
        ]),
        'facets' => new UserFacet([
            'name' => 'Type of Document', // Copied from facet builder
            'entries' => [
                '$contentTypeIdentifier' => '$count',
                // …
            ],
        ]),
        'facets' => new FieldFacet([
            'name' => 'Type of Document', // Copied from facet builder
            'entries' => 
        ]),
        // …
    ])

### Building Subsequent Searches

Now the user can select a facet which should be used for the further
search. The resulting contraint will be added to the `FacetBuilder`:

    $query->facetBuilders = [
        new FacetBuilder\ContentTypeFacetBuilder([
            'name' => 'Type of Document',
        ]),
        new FacetBuilder\UserFacetBuilder([
            'name' => 'Owner of Document',
            'type' => FacetBuilder\UserFacetBuilder::OWNER,
        ]),
        new FacetBuilder\FieldFacetBuilder([
            'name' => 'Color',
            'fieldPaths' => ['product/color'],
        ]),
    ];

    $query->facetBuilders[0]->limit('selectedContentTypeId');

The user can build dependent facets (a color facet might only make sense
when only products are selected) in their controller by adding
additional facets depending on the random constraints.

Multiple facets may have filters assigned (`->limit()`), each optionally
with multiple values. All those filters must be merged with the other
query filters. Containing the filters in the facet builds allows the UI
to be rendered with the appropriate filter selection.

### Facets

#### Facets by Aggregated Data Type

Facets returning a value (no reference to an entity):

- [DateRangeFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/DateRangeFacet.php)
- [FieldFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/FieldFacet.php)
- [FieldRangeFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/FieldRangeFacet.php)
- [TermFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/TermFacet.php)

Facets build upon entities:

- [UserFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/UserFacet.php)
- [LocationFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/LocationFacet.php)
- [SectionFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/SectionFacet.php)
- [ContentTypeFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/ContentTypeFacet.php)

Facet without any displayable data?

- [CriterionFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/CriterionFacet.php)

#### Factes by Display Type

List

- [FieldFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/FieldFacet.php)
- [TermFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/TermFacet.php)
- [UserFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/UserFacet.php)
- [LocationFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/LocationFacet.php)
- [SectionFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/SectionFacet.php)
- [ContentTypeFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/ContentTypeFacet.php)
- [CriterionFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/CriterionFacet.php)

Slider

- [DateRangeFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/DateRangeFacet.php)
- [FieldRangeFacet](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/FieldRangeFacet.php)

Display type selection is not a Public API concern but we should enable
developers to implement a sane selection. Right now `instanceof` checks
on the facade are probably sufficient. If more facet types are added we
can still add marker interfaces, like:

- RangeFacet
- ListFacet

Proposed Changes To The API
---------------------------

### Unsupported Facets

If a storage engine does not support facets it should just ignore them
and not throw a exception. The frontend code should expect that less
facets may be returned then facet builders were provided. Since we
propose to aggregate the facet builder in the facet (see below) this
hsould even be less of an issue now.

Given the user flow illustrated above there will be no filters added to
the facet because there are no facet values for the user to select from.

### Facet Name

I suggest to refactor the `Facet` base class; omit the `$name` property
and add a `$builder` property referencing the builder used to query the
facet. This allow to re-use the correct builder in therendering logic
and maybe reuse the criterion contained in the facet. This also allows
easy selection of the "active" list entry retrieved from the facet.

If we insist on maintaining BC we can keep the `$name` property in the
`Facet` class and just deprecate it. We can still fill it or dispatch it
to the respective property from the builder.

The `$name` püroperty in the `$builder` must be documented that it shall
contain a user provided name or identifier which is not cared about by
the API implementation.

**Implementation Note**

To be able to correlate the facet return value from the search backend with the
correct `FacetBuilder` we should generate a unique ID for each `FacetBuilder`
instance before executing the query and make the search engine return this ID
together with the facet return value.

On way to generate such an ID could be as simple as:

    foreach ($query->facets as $facetBuilder) {
        $facetBuilderMap[md5(serialize($facetBuilder))] = $facetBuilder;
    }

Instead of `serialize()` we might want to use `spl_object_hash()` or something
similar. This also tells apart multiple facet builders of the same base type
with different values (Different
[`UserFacetBuilder`](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/FacetBuilder/UserFacetBuilder.php)
instances, for example).

### Build Facet Filters

The `FacetBuilder` classes should retrieve a `limit()` method which
allows users of the API to easily apply a query filter for a certain
facet. The facet usually knows which `Criterion` to use for this.

The process thus is the following:

1)  The developer instantiates a `FacetBuilder` for a facet.
2)  The developer calls the `limit()` method with the facet value
    selected by the user.
3)  The `limit()` method add a criterion to the facet builder instance
    based on the type of the facet builder to the re-purposed `$filter`
    property of the facet builder.

After this process the `$filter` property of the `FacetBuilder` is
filled. The developer can, of course, still fill this property manually
instead of calling the `limit()` method.

All `$filter` properties from the facet builders will be merged (`AND`)
with the filter from the main `Query` by the SPI implementation.

### Facet Return Values

For the range facets the `$entries` already contained objects of a defined
class
([`RangeFacetEntry`](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Search/Facet/RangeFacetEntry.php)).
I suggest to define entry class for the other facets, too:

-   `ValueEntry` with the properties
    -   `$value`: string value found in the document
    -   `$count`: count of occurences
    -   `$selected`: flag if this is the queried value (to be marked
        "selected" in the view)
-   `EntityFacetEntry` extends `ValueEntry` with the additional
    properties:
    -   `$entity`: Instance of the referenced entity, for example the
        ContentType

The new property in the Facet classes containing the entries will be
called `$results` to not break BC. We will immediately deprecate the
`$entries` array following the old, loosely defined, format.

### Original `FacetBuilder->$filter`

Change thu purpose of this property.
[ElasticSearch](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-filter-aggregation.html)
implements aggregation filters to further limit an aggregation (facet)
with an additional filter (set of filters). Together with the global
flag this can be used to list facet values in multi-select fields, but
we probably should not expose this ElasticSearch feature through our API
since it seems specific and has no direct counterpart in Solr (as far as
we know).

The `$filter` property will be used for the facet criterion.

From the documentation this is a a BC break. Since the `$filter`
property is yet unused this should be OK. We still want to use the
`$filter` property since the property with the same purpose is also
called `$filter` on the `Query` class. Those filters will be merged and
should thus be called the same.

### `FacetBuilder->$global`

Omit (deprecate) this property. The idea behind this was to get the
facet values across the full data and not get the facet results for the
current search. As far as I can see there is no such option in Solr.
Thus this would require an extra query. If there is no strong user
demand for this we should just omit the parameter. This is supported by
[ElasticSearch](https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-global-aggregation.html)
but not by other search backends.

If a user still wants this result they can always run a dedicated search
without any criteria and the seeked "global" facets. Thus there is a
migration path for a yet non working feature requiring no additional
implementation by us.

### Multi-Select Facets

In some use cases the following requirement occurs:

> The user wants to query the content. The result shall contain a facet
> across some property, for example the publishing date. The user
> selects a first facet criterion (one day) and then wants to select an
> additional facet criterion (another day).

With the current implementation selecting the first value will only show
content following the first selected facet criterion. Thus facet value
mutli select does not work.

We could add a property to the facet builder which enables retrieval of
all facet values (limited by the common query / filter) even a facet
value is already selected. This can be implemented against ElasticSearch
using a global aggregation with a aggregation filter containing only the
query filter and **not** the facet filter itself while the content
filter will still be build from the query filter **and** the facet
filter.

This can be implmented against other search backends, too (maybe using
additional queries) while this might be harder for the `$global` and
`$filter` properties already existing in the `FacetBuilder`.

### Facet Value Storage

We should only store the actual IDs in the search backends, not the
identifier. If a criterion is missing to query that ID we should just
implement this (`SectionIdCriterion`). The queries are supposed to be
more performant this way and it spares us re-indexing of the search
index if identifiers change.

### Additional Facets

We probably can create additional facets for alost every criterion. This
means there are obvioulsy missing ones:

- [LanguageCode](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/Criterion/LanguageCode.php)
- [ContentTypeGroup](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/Criterion/ContentTypeGroupId.php)
- [ParentLocation](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/Criterion/ParentLocationId.php)
- [LocationDepth](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/Criterion/Location/Depth.php)
- [ObjectState](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/Criterion/ObjectStateId.php)
- [Visibility](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/Criterion/Visibility.php)

Not sure if this can work, would be a range facet, though. At least
ElasticSearch has a "Geo Distance Aggregation":

- [MapLocationDistance](/ezsystems/ezpublish-kernel/tree/master/eZ/Publish/API/Repository/Values/Content/Query/Criterion/MapLocationDistance.php)

