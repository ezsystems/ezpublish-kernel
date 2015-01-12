@contentTypeGroup
Feature: Read a Content Type Groups
    As a developer
    I need to be able to read a Content Type Group
    In order to know use it

    # Get Content Type Group through <ID>
    Scenario: Read a Content Type Group through id
        Given I have "administrator" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        When I create a "GET" request to "/content/typegroups/{id}"
        And I set header "accept" for "ContentTypeGroup" object
        And I send the request
        Then response has a Content Type Group with identifier "some_string"
        And response has a "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object
        And response header "content-type" has "ContentTypeGroup"

    Scenario: Get relevant information when attempting to read a non existing id
        Given I have "administrator" permissions
        And there isn't a Content Type Group with id "{id}"
        When I get Content Type Group with id "{id}"
        Then response has a not found exception

    Scenario: Read a Content Type Group through id with anonymous user
        Given I have "anonymous" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        When I get Content Type Group with id "{id}"
        Then response has a Content Type Group with identifier "some_string"
        And response has a "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object
        And response header "content-type" has "ContentTypeGroup"

    # Get Content Type Group through <identifier>
    Scenario: Read a Content Type Group through identifier
        Given I have "administrator" permissions
        And there is a Content Type Group with identifier "some_string"
        When I create a "GET" request to "/content/typegroups?identifier=some_string"
        And I set header "accept" for "ContentTypeGroup" object
        And I send the request
        Then response contains Content Type Group with identifier "some_string"

    Scenario: Get relevant information when attempting to read a non existing identifier
        Given I have "administrator" permissions
        And there isn't a Content Type Group with identifier "some_string"
        When I get Content Type Group with identifier "some_string"
        Then response has a not found exception

    Scenario: Read a Content Type Group through identifier with anonymous user
        Given I have "anonymous" permissions
        And there is a Content Type Group with identifier "some_string"
        When I get Content Type Group with identifier "some_string"
        Then response has a Content Type Group with identifier "some_string"
        And response has a "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object
        And response header "content-type" has "ContentTypeGroup"
