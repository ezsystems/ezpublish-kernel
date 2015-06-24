# Listing Content and Locations

Starting from Platform 2015.07, listing content and locations in a project is
much easier, using a couple standardized mechanisms.

Predefined, named queries can be defined in any bundle. They can then be used from twig, using a
[custom controller](#query_controller), or from PHP code using the [QueryType Registry](#fixme). The controller supports
[template overriding](#template_override), making it easy to use different templates for different calls.

## <a name="query_controller"></a> The query controller
A new controller action is added to the `ez_content` controller: `query`.

It requires the name of the QueryType to run. It also accepts a hash of parameters if the QueryType supports any.

```twig
{{ render( controller( 'ez_content:query', {'query_name': 'AcmeBundle:LatestArticles'})) }}
```

### <a name="template_override"></a>Rendering of queries
The `ez_content:query` action supports template override.

## QueryType objects
Query Types are predefined Content or Location queries registered with a unique
name. They can be used in different ways in order to facilitate listing items.

A QueryType must implement the QueryType interface (`eZ\Publish\Core\MVC\Query\QueryType`).
It has two methods:
- `getQuery( array $parameters = null )`: returns the contained Query
- `getName()`: returns the QueryType name

`getQuery()` accepts an optional hash of parameters. It can be used in any way
in the method:
- to customize an element's value (limit, ContentType identifier, etc)
- to conditionally add/remove criteria from the query

### QueryType example: latest articles
This QueryType contains a Query that returns the 10 last published articles.

```php
<?php
namespace AcmeBundle\eZ\Query;

use eZ\Publish\Core\MVC\Query\QueryType;

class LatestArticlesQueryType implements QueryType
{
  public function getQuery( array $parameters = [] )
  {
    $query = new LocationQuery();
    $query->criterion = new Criterion\LogicalAnd([
      $criteria[] = new Criterion\Visibility( Criterion\Visibility::VISIBLE );
      $criteria[] = new Criterion\ContentTypeIdentifier( 'article' );
      $criteria[] = new Criterion\ParentLocationId(2);
    ]);
    $query->sortClauses = [new SortClause\DatePublished()];
    $query->limit = isset($parameters['count']) ? $parameters['count'] : 10;

    return $query;
  }
}
```

### Declaring a custom QueryType
In addition to creating a PHP class for a `QueryType`, it must also be registered
within the Service Container. This can be done in two ways: by convention, and
with a service tag.

#### By convention
Any class named `AcmeBundle\eZ\QueryType\*QueryType`, that implements
the QueryType interface, will be registered as a
custom QueryType.

#### Using a service tag
```yaml
acme.query.latest_articles:
    class: AcmeBundle\Query\LatestArticles
    tags:
        - {name: ez_query}
```

