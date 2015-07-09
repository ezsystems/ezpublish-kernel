Again, should we distinguish QueryType by Content and Location ?

If we do, do we need/have two ContentTypeRegistry, one for content, one for location ?
Is that a problem ? Used from the `ez_named_query` controller, not really: since the design/template is supposedly mapped by configuration (override rules), you don't need to know the Query's type. The rendering would be able to choose transparently, and use the right template.

IF rendering of a Search Result is *meant* (e.g. in the most common/important cases) to be done through the override mechanism, then in this mechanism, we don't need to care which type of Query we're dealing with: the rendering thing will.

```yml
system:
    default:
        content_query:
            # view type
            list:
                
                latest_articles:
                    template: AcmeBundle:query/location/latest_articles.html.twig
                    match:
                        Identifier\QueryType: AcmeDemoBundle:LatestArticles
```

A lower level usage of QueryType objects is still possible, by using the (Named)QueryTypeRegistry:

```php
$queryType = $registry->getQueryType( 'AcmeBundle:LatestArticles' );
$parameters = [];
$searchResults = $searchService->findContent( $queryType->getQuery( $parameters ) );
```
