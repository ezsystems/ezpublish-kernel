## Overview

> Target audience: project maintainers, dev contributors

Named queries are Content or Location Query objects that are predefined in the system.

They can be used in multiple ways:
- From Twig templates, using a sub-request: `{{ render( controller( ez_query, {'identifier': 'latest_articles' ) ) }}`.
- From php code, by getting the Query from a a QueryType object.

## Components

### Query controller

```twig
{{ render( controller( ez_query, {'identifier': 'latest_articles', 'parameters': {'type': 'article'}} ) ) }}
```

#### Templating

Possible options, by order of priority
- Controller parameter that forces the used template
- Override rule. Add a new config level in addition to content and location (query ?).
- Defined in the QueryType class, maybe with a dedicated, optional interface (`getTemplate()`)

### Services

Automatically generated services, per query: `ezplatform.query.latest_articles`. Note that this would confict with
the namespacing concern mentioned below.

## Defining QueryType objects

### Code

Queries can be declared as files and registered into the service container (service tag / naming convention)

## Ideas and open questions

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

### Expression language

It could be used as a shortcut to get the Query itself, optionally.

### Query types

How do we distinguish/handle Location vs Content queries ?
