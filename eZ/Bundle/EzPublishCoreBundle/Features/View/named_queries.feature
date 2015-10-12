Feature: QueryTypes results can be displayed using templates and controllers

    Scenario: Content lists can be rendered using a default template
       Given that there is a QueryType "EzPlatformBehatBundle:LatestContent" with a "type" parameter
         And running a content search from that QueryType with the parameter "type" set to "article" returns search results
         And there is a "latest_articles" query_type_view configuration for the "full" viewType
         And that configuration has "template" set to "EzPlatformBehatBundle:full:query_latest_article.html.twig"
         And that configuration has "enable_pager" set to the boolean "true"
         And that configuration matches the QueryType name "EzPlatformBehatBundle:LatestContent"
         And that configuration matches the QueryType parameter "type" with the value "article"
         And there is a route "ez_platform_behat_latest_articles"
         And that route has the default "_controller" set to "ez_query:content"
         And that route has the default "queryTypeName" set to "EzPlatformBehatBundle:LatestContent"
         And that route has the default "viewType" set to "full"
         And that route has the default "parameters" set to an array with the key "type" set to "article"
        When I go to that route
        Then I see the QueryType results listed with this template


