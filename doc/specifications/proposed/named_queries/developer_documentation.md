# Listing Content and Locations

Starting from Platform 2015.07, listing content and locations in a project is
much easier, using a couple standardized mechanisms.

Predefined, named queries can be defined in any bundle. They can then be used from twig, using a
[custom controller](#query_controller), or from PHP code using the [QueryType Registry](#fixme). The controller supports
[template overriding](#template_override), making it easy to use different templates for different calls.

## <a name="query_controller"></a> The query controller
A new controller action is added to the `ez_content` controller: `query`.

It requires two arguments:
- `queryName`, the name of the QueryType to run. Example: `AcmeBundle:LatestArticles'
- `viewType`, similar to what `viewContent` and `viewLocation` expect

If the QueryType supports any, it also accepts a `parameters`, a hash of parameters:

```twig
{{ render( controller(
    'ez_content:query',
    {
        'queryName': 'AcmeBundle:LatestContent',
        'viewType': 'list',
        parameters: { ContentType: 'article' }
)) }}
```

### <a name="template_override"></a>Rendering of queries
The `ez_content:query` action supports template override. It supports two matchers:
- `QueryType\Name`: matches the QueryType's name
- `QueryType\Parameter`: matches the value of one of the parameters passed to the QueryType
  The expected format is a hash with the keys `name` and `value`.

Assuming we want to render the `AcmeBundle:LatestContent` query using
`AcmeBundle:query/list/latest_articles.html.twig` when the `ContentType` parameter has the value 'article':

```twig
system:
    default:
        query:
            list:
                latest_articles:
                    template: "AcmeBundle:query/list/latest_articles.html.twig"
                    match:
                        QueryType\Name: 'AcmeBundle:LatestContent'
                        QueryType\Parameter: "ContentType=article"
```

## QueryType objects
To make a new QueryType available to the Query Controller, you need to register a new
QueryType and register it.

A QueryType is a PHP class that implements QueryType interface (`eZ\Publish\Core\MVC\Query\QueryType`).
It has two methods:
- `getName()`: returns the QueryType name
- `getQuery( array $parameters = null )`: returns the contained Query.
  It accepts an optional hash of parameters that can be used in any way in the implementation:
  - customize an element's value (limit, ContentType identifier, etc)
  - conditionally add/remove criteria from the query
  - set the limit/offset...


### QueryType example: latest content
This QueryType contains a Query that returns the 10 last published content, order by reverse
publishing date. It accepts an optional `ContentType` parameter, that can be set to a ContentType
identifier:

```php
<?php
namespace AcmeBundle\eZ\Query;

use eZ\Publish\Core\MVC\Query\QueryType;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class LatestContentQueryType implements QueryType
{
  public function getQuery( array $parameters = [] )
  {
    $query = new LocationQuery();
    $query->criterion = new Criterion\LogicalAnd([
      $criteria[] = new Criterion\Visibility( Criterion\Visibility::VISIBLE );
      $criteria[] = new Criterion\ContentTypeIdentifier( 'article' );
      $criteria[] = new Criterion\ParentLocationId(2);
      $criteria[] = new Criterion\Location\Depth( Criterion\Operator:GT, 1 )
    ]);
    $query->sortClauses = [new SortClause\DatePublished()];
    $query->limit = 10;

    return $query;
  }

  public function getName()
  {
    return 'LatestContent';
  }
}
```

### Registering a QueryType
In addition to creating a PHP class for a `QueryType`, it must also be registered
within the Service Container. This can be done in two ways: by convention, and
with a service tag.

#### By convention
Any class named `AcmeBundle\eZ\QueryType\*QueryType`, that implements
the QueryType interface, will be registered as a
custom QueryType.

#### Using a service tag
```yaml
acme.query.latest_content:
    class: AcmeBundle\Query\LatestContent
    tags:
        - {name: ez_query}
```

