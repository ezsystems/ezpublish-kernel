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
