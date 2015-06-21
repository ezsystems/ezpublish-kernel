# REST Views

## Status
> Needs more work

An application layer feature served by a repository endpoint is probably not right, but we need to make those usable
over REST somehow.

## Getting a view's results
Returns the same results that POSTing a Query to `/content/views`,  but without the need to POST the Query. Results can
also be cached over HTTP.

```
GET /api/ezp/v2/content/views/AcmeBundle:LatestArticles
```

### Passing parameters
QueryTypes/Views can define custom parameters, optional or not, that can be given a custom value.

Over REST, View arguments are passed as query parameters:
```
GET /api/ezp/v2/content/views/AcmeBundle:LatestArticles?category=marketing
```

In the example above, category could be mapped to a Location ID, or a Content Field, either tag, or selection,
or object relation...
