@contenttypegroup @restApi
Feature: Creating a Content Type Group
    Create Content Type Group
    As an administrator or anonymous user
    I want to create a Content Type Group

    @qa-258
    Scenario Outline: Create Content Type Group through the valid body types
        Given I am logged in as an "administrator"
        When I make a "POST" request to "/content/typegroups"
        And I add to request the headers:
            | header       | value                                       |
            | Accept       | application/vnd.ez.api.ContentTypeGroup+xml |
            | Content-Type | <content-type>                              |
        And I add to request body a "Create ContentTypeGroup" in <type>
        And I send the request
        Then I see response body in "<type>"
        And I see a "ContentTypeGroup" in response

        Examples:
            | content-type                                      | type |
            | application/vnd.ez.api.ContentTypeGroupInput+json | json |
            | application/vnd.ez.api.ContentTypeGroupInput+xml  | xml  |

    @qa-258
    Scenario Outline: Read response header
        Given I am logged as an "administrator"
        When I make a "POST" request to "/content/typegroups"
        And I add to request the header "Content-Type" with "application/vnd.ez.api.ContentTypeGroupInput+<type>"
        And I add to request body a "Create ContentTypeGroup" in <type>
        When I send the request
        Then I see a "201 Created" response code
        And I see response headers:
            | header       | value                                               |
            # for the <id> on next sentence we need to make it generic on the backend
            | Location     | /content/type/\\<id\\>                              |
            | Accept-Patch | application/vnd.ez.api.ContentTypeGroupInput+<type> |
            # Headers Content-Type and Content-lenght not asserted on this scenario

        Examples:
            | type |
            | json |
            | xml  |

    @qa-258
    Scenario Outline: Ask for possible responses when creating a Content Type Group
        Given I am logged as an "administrator"
        When I make a "POST" request to "/content/typegroups"
        And I add to request the headers:
            | header       | value                                             |
            | Accept       | <accept>                                          |
            | Content-Type | application/vnd.ez.api.ContentTypeGroupInput+json |
        And I add to request body a "Create ContentTypeGroup" in "json"
        And I send the request
        Then I see a <response-type> in response
        And I see response in <type>
        And I see response header "Content-Type" with <accept>

        Examples:
            | accept                                       | type | response-type    |
            | application/vnd.ez.api.ContentTypeGroup+xml  | xml  | ContentTypeGroup |
            | application/vnd.ez.api.ContentTypeGroup+json | json | ContentTypeGroup |

    @qa-258
    Scenario: Attempt to create an Content Type Group with an not authorized user
        Given I am an anonymous visitor
        When I make a "POST" request to "/content/typegroups"
        And I add to request the header "Content-Type" with "application/vnd.ez.api.ContentTypeGroupInput+xml"
        And I add to request body a "Create ContentTypeGroup" in "xml"
        And I send the request
        Then I see "401 Not Authorized" response code

    @qa-258
    Scenario: Attempt to create an Content Type Group with invalid body
        Given I am logged as an "administrator"
        When I make a "POST" request to "/content/typegroups"
        And I add to request the header "Content-Type" with "application/vnd.ez.api.ContentTypeGroupInput+json"
        And I add to request body an invalid "Create ContentTypeGroup" in "json"
        And I send the request
        Then I see "400 Bad Request" reponse code

    @qa-258
    Scenario Outline: Attempt to create a Content Type Group with body type different from Content-Type header
        Given I am logged as an "administrator"
        When I make a "POST" request to "/content/typegroups"
        And I add to request the header "Content-Type" with "application/vnd.ez.api.ContentTypeGroupInput+<content-type>"
        And I add to request body a "Create ContentTypeGroup" in <body-type>
        And I send the request
        Then I see "400 Bad Request" reponse code

        Examples:
            | body-type | content-type |
            | xml       | json         |
            | json      | xml          |

    @qa-258
    Scenario: Attempt to create a Content Type Group with same identifier as an existing one
        Given I am logged as an "administrator"
        And I have a ContentTypeGroup with:
            | field      | value        |
            | identifier | existing-one |
        When I make a "POST" request to "/content/typegroups"
        And I add to request the header "Content-Type" with "application/vnd.ez.api.ContentTypeGroupInput+json"
        And I add to request body a "Create ContentTypeGroup" with:
            | field      | value        |
            | identifier | existing-one |
        And I send the request
        Then I see "403 Forbidden" reponse code

