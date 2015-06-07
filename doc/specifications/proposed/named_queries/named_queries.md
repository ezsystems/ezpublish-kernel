## Overview

Named queries are Content or Location Query objects that are predefined in the system.

They can be used in multiple ways:
- from twig templates, using a sub-request: {{ render( controller( ez_query, {'identifier': 'latest_articles' ) ) }}
- from php code, by getting the Query from a service
- from REST, by executing a predefined view / query

## Components

### REST

#### Getting a view's results
Returns the same results that POSTing a Query to `/content/views`,  but without the need to include the Query in the Request.
```
GET /api/ezp/v2/content/views/AcmeBundle:LatestArticles
```

##### Passing parameters
QueryTypes/Views can define custom parameters, optional or not, that can be given a custom value.

Over REST, View arguments are passed as query parameters:
```
GET /api/ezp/v2/content/views/AcmeBundle:LatestArticles?category=marketing
```

In the example above, category could be mapped to a Location ID, or a Content Field, either tag, or selection, or object relation...


### Query controller
```twig
{{ render( 
	controller( 
		ez_query, 
		{
			'identifier': 'latest_articles', 
			'parameters': {'type': 'article'}
		} 
	) 
) }}
```

### Services

A main `QueryService` / `ezplatform.query_service`, with methods to load a QueryType by identifier. Query persistence will be specified later.

Automatically generated services, per query: `ezplatform.query_type.latest_articles`
(do we need an underscore separated automatically generated name ? The QueryType class would be `LatestArticlesQueryType`.

### Services

#### QueryTypeService
A main `QueryTypeService` / `ezplatform.query_type_service`, with a `load` method a QueryType by identifier. Query persistence will be specified later.

#### QueryType auto-services
QueryType objects are, via a compiler pass, registered as services. It makes it easy to just inject one into a controller or any business logic item without injecting the QueryTypeService.

Example: `ezplatform.query.latest_articles`

Q: Do we need an underscore separated automatically generated name ? The QueryType class would be `LatestArticlesQueryType`, how do we go to `latest_articles` ?

## QueryType object

QueryTypes are PHP classes that implement the `QueryType` interface, and are registered into the service container.

### QueryType identifiers
QueryTypes are identified using the bundle's name and the QueryType's name itself. This is done to prevent conflicts between QueryTypes from different bundles.

Example: `AcmeBundle:LatestArticles`

### Service container registration

#### Service tag
> tag name: `ez_query`

The service alias depends on the class and on the bundle.

#### Naming convention
In a bundle, a file named `eZ/QueryType/{Something}QueryType.php`, with a matching class name, will be registered and tagged as an eZ Query.

### Interface
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
}
```

#### Parameters
QueryTypes that support parameters must implement the NAME-ME interface. It has a `getDefinedParameters()` that returns an array.

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

#### Example: latest articles

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
				// Could come from siteaccess aware config
				criteria[] = new Criterion\ParentLocationId(42);
            ]
        );
        $query->sortClauses = [new SortClause\DatePublished()];
        $query->limit = 10;
        return $query;
    }
}
```

## Ideas and open questions

### Expression language
It could be used as a shortcut to get the Query itself, optionally.

### Query types
How do we distinguish/handle Location vs Content queries ?

SubClass the main QueryType as LocationQueryType and ContentQueryType ? The REST API distinguishes them as follows:
- The XML/JSON node representing the Query is either a LocationQuery or a ContentQuery
- The result with either contain Content or Location objects (the endpoint chooses the right Search method depending on the type of Query.
