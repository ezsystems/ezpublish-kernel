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

