# QueryType API

A QueryType returns a named, predefined Query, based on optional parameters.

## QueryType identifiers
QueryTypes are identified using the bundle's name and the QueryType's name itself. This is done to prevent conflicts between QueryTypes from different bundles.

Example: `AcmeBundle:LatestArticles`

## Interface
```php
/**
 * A QueryType is a registered Repository Search Query.
 */
interface QueryType
{
  /**
   * Builds and returns the Query object
   * @param array $parameters query parameters, if any was defined in the QueryType
   * @return \eZ\Publish\API\Repository\Values\Content\Query
   */
  public function getQuery( array $parameters = [] );

  /**
   * Returns the QueryType's unique identifier.
   * @return string
   */
  public function getIdentifier();
}
```

### Implementation example: latest articles

```php
class LatestArticlesQueryType implements LocationQueryType
{
    public function getQuery( array $parameters = [] )
    {
        $query = new LocationQuery();
        $query->criterion = new Criterion\LogicalAnd(
            [
                $criteria[] = new Criterion\Visibility( Criterion\Visibility::VISIBLE );
                criteria[] = new Criterion\ContentTypeIdentifier( 'article' );
				// Could be injected from siteaccess aware config
				criteria[] = new Criterion\ParentLocationId(42);
            ]
        );
        $query->sortClauses = [new SortClause\DatePublished()];
        $query->limit = 10;
        return $query;
    }
}
```

### Parameter handling

#### Unique interface

#### Dedicated interface for a QueryType with parameters ?
QueryTypes with  parameters support could implement a specific interface.
It could have a a `getDefinedParameters()` method.

```php
interface ConfigurableQueryType
{
    /**
     * Returns the list of defined parameters for the QueryType
     * @return array
     */
    public function getDefinedParameters();
}
```

> Q: what about required/optional parameters ? what about parameters types (array, scalar mostly) ?

Problem: in that case, we should remove the `$parameters` argument from the base `QueryType` interface. But then,
we can't add the argument to the `ConfigurableQueryType`'s `getQuery()` method.

We could use another method (`executeWithParameters()`), but it sounds a bit too strict from a DX perspective

## Service container registration

### Service tag
> tag name: `ez_named_query`

The service alias depends on the class and on the bundle.

> Question: any point in the getIdentifier method documented in the interface ? Yes, as external usages of a QueryType
> (think override rules) could require it. It could be implemented in an/the base `NamedQueryType` abstract class, as
> a `final` method. But how do we get/inject the bundle's string/name to generate the ID ?

### Naming convention
In a bundle, a file named `eZ/QueryType/{Something}QueryType.php`, with a matching class name, will be registered and tagged as an eZ Query.

## Services

### Main QueryTypeRegistry

> Status: must have

A main `QueryTypeRegistry` / `ezplatform.query_type.registry`, with a `load` method that accepts a QueryType identifier.

(REST: No store, delete or update methods as persistence will be handled later)

#### Per query service

> Status: questionable (naming is an issue if we want to follow namespacing)

Automatically generated services (compiler pass), per query: `ezplatform.query_type.latest_articles`
(How do we get from `AcmeBundle:LatestArticles` to a usable service name ? text transformation ?)

