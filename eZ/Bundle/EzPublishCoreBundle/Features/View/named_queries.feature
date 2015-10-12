Feature: QueryType objects can be used with Views

    Scenario: ez_query controller actions can be overridden
       Given that a query_type view matches on a QueryType name
         And that a QueryType with that name exists
        When ez_query:content is rendered with that QueryType name
        Then the search results from that QueryType are assigned to the template as content_list

    Scenario: When enable_pager is set to false in a QueryType View, then search results are passed to the template as an array
       Given that a query_type view has enable_pager set to false
        When that view is rendered
        Then the query results are assigned to the template as an array

    Scenario: When enable_pager is not specified in a QueryType View, then search results are passed to the template as a pager object
        Given that a query_type view does not specify enable_pager
        When that view is rendered
        Then the query results are assigned to the template as a PagerFanta Pager object
