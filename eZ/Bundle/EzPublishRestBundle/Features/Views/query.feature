@views
Feature: Running searches using REST views

    Scenario: Running a ContentQuery returns the expected results
       Given I have "administrator" permissions
        When I create a "POST" request to "/views"
         And I set the Content-Type header to "application/vnd.ez.api.ViewInput" in version "1.1"
         And I set header "accept" for "View" object
         And I make a "ViewInput" object
         And I set field "identifier" to "some_identifier"
         And I set field "contentQuery" to a Query object
         And I set the "filter" property of the Query to a valid Criterion
         And I send the request
        Then response status code is 200
         And response contains a "eZ\Publish\Core\REST\Client\Values\View" object
         And the View contains Search Hits
         And the Search Hits are Content objects
