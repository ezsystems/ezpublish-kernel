# The View API

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

### API

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

> Q: what is the link between a ViewResult and its View ? (to re-execute, for paging for instance)
> Q: does the ViewResult add anything to the existing SearchResult ?
