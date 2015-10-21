Feature: The ez_query controller can be used in content views to get query results into a view template

    Scenario: A content view that uses the query controller...
        Given there is a blog content_view configuration
          And it sets the controller to 'ez_query:contentAction'
          And it sets the parameter "query" to a valid QueryType name
         When a content matching the blog configuration is rendered
         Then the view template has an 'items' variable
          And the 'items' variable is an array with the results from the queryType

    Scenario: The template variable search results are assigned to can be customized

    Scenario: Parameters can be passed to the QueryType

    Scenario: Parameters from the view object can be passed to the QueryType
