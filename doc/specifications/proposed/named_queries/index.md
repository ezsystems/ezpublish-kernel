# Named queries

## Overview

Named queries, or QueryTypes, are predefined Content or Location Query objects. They can be used in several ways:
- from twig templates, using a sub-request:
  `{{ render( controller( ez_query, {'identifier': 'latest_articles' ) ) }}`
- from php code, by loading the Query, and running it through the SearchService
- from REST, using `/content/views` (to be confirmed)

QueryTypes can accept parameters, that can be used as values in the query. It can be used for paging, or to set a
ContentType identifier...

In the initial version, QueryType objects can be created as PHP classes. The main use-case is to allow developers
to ease implementation of content / location lists in sites implementations, by defining QueryTypes inside bundles.

### Differences from Persisted Queries

When `Views` were added to REST API there was the intention to allow these to be persisted at some point *(after UI were moved to Platform Stack)*.
And while there are no own specification for it yet, these are the main differences between the two:
- Persisted Queries serves as Editorial Queries/Search to drive possible future features like reuse in content, blocks, and plainly sharing link to a search result to co workers or public.
- Named Queries on the other side is a DX feature for developer to provided queries for reuse in code, and aim to make eZ Platform much easier to work with by exposing the power of `filtering` content directly from templates.
- Persisted Queries, unlike Named Queries, will by it's nature be possible to be retrieve and created from PHP Repository API, and hence also REST API which will need to distinguishing these two from each other for API use and in extension UI's exposing such editing functionality.
- Persisted Queries could allow the Repository to keep track of hits internally in the future, ala how JIRA works with filters. This will allow more advance cache clearing logic to aim for editor never having to take care about cache.
- Persisted Queries being editorial and not a DX feature like Named Query, meaning editor might change it completely at any moment, and also for cache reasons mentioned above this means it most likely won't support dynamic parameters beyond limit, offset and viewMode *(does not belong to Query, rather view and would be exposed by embeds and blocks)*.

However Persisted Queries are a non-goal of this spec and feature, this section is merely to make it's relation more clear as there are many possible overlaps in feature-set for Queries and Views.


## Chapters

### [Query Type API](query_type_api.md)
The QueryType API (interfaces, services) and the resulting NamedQueryType implementation.

### [The Query Controller](query_controller.md)
Controller that renders the results of running a QueryType through the SearchService.

### [REST Views](rest_views.md)
REST integration of QueryType/NamedQueryType.

### [The View API](view_api.md)
PHP counterpart of REST Views. Based around a View object that encapsulates a QueryType/a Query.

### [Drafts and questions](drafts_and_questions.md)
Unsorted topics.
