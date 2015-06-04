## Overview

Named queries are Content or Location Query objects that are predefined in the system.

They can be used in multiple ways:
- from twig templates, using a sub-request: {{ render( controller( ez_query, {'identifier': 'latest_articles' ) ) }}
- from php code, by getting the Query from a service
- from REST, by executing a predefined view / query

They
## Components

### REST

All REST calls on `/content/views`.

### Query controller
```twig
{{ render( controller( ez_query, {'identifier': 'latest_articles', 'parameters': {'type': 'article'}} ) ) }}
```

### Services

A main `QueryService` / `ezplatform.query_service`, with methods to load/create/update/delete named queries.
Automatically generated services, per query: `ezplatform.query.latest_articles`

## Persistence of views/queries

### Code

> Target audience: project maintainers, dev contributors

Queries can be declared as files and registered into the service container (service tag / naming convention)

## Ideas

### Query as a service

Queries could be, via a compiler pass, made available as services. It would make it easy to just inject one into
a controller (would replace various helpers).

Query parameters could then be passed to the `getQuery()` method, as a hash:

```
$query->getQuery( ['content_type' => 'article', 'limit' => 20] );
```

### Query parameters

### Namespacing
Should we document/enforce some kind of namespacing of queries, in order to limit conflicts ?
Should we reference *code based queries* using the bundle's name, like `eZDemoBundle:latest_articles` ? It would prevent
all issues, would it not ?
