# Listing Content and Locations

Starting from Platform 2015.07, listing content and locations in a project is
much easier, using a couple standardized mechanisms.

## QueryType objects
Query Types are predefined Content or Location queries registered with a unique
name. They can be used in different ways in order to facilitate listing items.

A QueryType must implement the QueryType interface (`eZ\Publish\Core\MVC\Query\QueryType`).
It has two methods:
- `getQuery( array $parameters = null )`: returns the contained Query
- `getName()`: returns the QueryType name

### QueryType example: latest articles
This QueryType contains a Query that returns the 10 last published articles.

```php
namespace AcmeBundle\eZ\Query;

use eZ\Publish\Core\MVC\Query\QueryType;

class LatestArticlesQueryType implements QueryType
{
    public function getQuery( array $parameters = [] )
    {
        $query = new LocationQuery();
        $query->criterion = new Criterion\LogicalAnd([
            $criteria[] = new Criterion\Visibility( Criterion\Visibility::VISIBLE );
            criteria[] = new Criterion\ContentTypeIdentifier( 'article' );
    				// Could be injected from siteaccess aware config
    				criteria[] = new Criterion\ParentLocationId(42);
        ]);
        $query->sortClauses = [new SortClause\DatePublished()];
        $query->limit = 10;
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
the `eZ\Publish\Core\MVC\Query\QueryType` interface, will be registered as a
custom QueryType.

#### Using a service tag
```yaml
acme.query.latest_articles:
    class: AcmeBundle\Query\LatestArticles
    tags:
        - {name: ez_query}
```

## The query controller
A new controller action is added to the `ez_content` controller: `query`.
It can be given

```twig
{{ render( controller( 'ez_content:query', {'query_name': 'AcmeBundle:LatestArticles'})) }}
```

### Customizing the template
`ez_content:query` also supports

## QueryType objects
