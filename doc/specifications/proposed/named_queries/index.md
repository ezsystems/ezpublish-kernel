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
