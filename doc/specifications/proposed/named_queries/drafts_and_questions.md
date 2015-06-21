## Ideas and open questions

### Expression language
It could be used as a shortcut to get the Query itself, optionally.

### Query types
How do we distinguish/handle Location vs Content queries ?

SubClass the main QueryType as LocationQueryType and ContentQueryType ? The REST API distinguishes them as follows:
- The XML/JSON node representing the Query is either a LocationQuery or a ContentQuery
- The result with either contain Content or Location objects (the endpoint chooses the right Search method depending on the type of Query.

### Caching
If we want REST views to be cacheable, we should add the relevant X-Location-ID headers in the result.

> Q: Is it something we want ?

### Dependency on REST LocationSearch
LocationQueries will obviously not work until LocationSearch support is added to REST.

### Should we separate content & location search queries ?

On second thought, having one feature handling both queries (over REST and PHP) could get tedious.
Maybe not so much over REST, since you will get the ContentType, and the Query has already been executed.

Another way would be to distinguish the registry's methods: `QueryTypeRegistry::loadContentQuery`, or `QueryTypeRegistry::loadLocationQuery`.
But this would kind of imply *two* registries.

## What about View objects in PHP ?

For PHP developers, you'd have to test what kind of query you got, in order to send it to the right SearchService
method. To be on par with REST, one way would be to add a View object.

Either it's a Value object that contains the query, and can execute itself, or a dedicated service, that can be given a
Query, and will be returned the results. And if the View was an iterator, it could really make a few things easier.

Though you'd still have to test what the query contains before you can use it, but you also have to do this with REST.

Or we just need separate `LocationView` and `ContentView` objects in PHP as well. But then, how do you load those views ?
Then maybe we instead need a ViewTypeRegistry instead of a QueryTypeRegistry.

### Usage example

```php
$view = new View(
    $queryTypeRegistry->get( 'AcmeBundle:latestArticles` )
);

$viewResult = $view->execute( ['category' => 'marketing'] );
foreach ( $viewResult as $result )
{
  // ...
}
```

### View objects

#### View

A View is a stateless QueryType container. It can execute the query, with or without parameters, and returns a ViewResult.
It has the requirements (SearchService, etc) to execute Queries.

```php
interface View
{
    /**
     * @return Query
     */
    public function getQuery();

    /**
     * @return ViewResult
     */
    public function execute( array $parameters = [] );
}
```

#### ViewResult

A ViewResult contains the results of the execution of a view. It acts as a proxy to the the API's SearchResult object.
It may provide automatic paging of results by automatically managing the offset/limit Query Parameters (this is something
the View object can not do, as it is stateless).


```
interface ViewResult implements Countable, Iterator
{
}
```

> Q: what is the link between a ViewResult and its View ?
> Q: does the ViewResult add anything to the existing SearchResult ?
