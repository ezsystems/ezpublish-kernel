@contentTypeGroup
Feature: Create Content Type Group
    As a developer
    I need to create a Content Type Group
    In order to make a group for Content Types

    Scenario: Create a valid Content Type Group
        Given I have "administrator" permissions
        And there isn't a Content Type Group with identifier "some_string"
        When I create a "POST" request to "/content/typegroups"
        And I set header "content-type" with "ContentTypeGroupInput" object
        And I set header "accept" for "ContentTypeGroup" object
        And I make a "ContentTypeGroupCreateStruct" object
        And I set field "identifier" to "some_string"
        And I send the request
        Then response has a Content Type Group with identifier "some_string"
        And Content Type Group with identifier "some_string" was created

    Scenario: Get relevant information after creating the Content Type Group
        Given I have "administrator" permissions
        And there isn't a Content Type Group with identifier "some_string"
        When I create a Content Type Group with identifier "some_string"
        Then response status code is 201
        And response status message is "Created"
        And response header "content-type" has "ContentTypeGroup" object
        And response has a "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object
        And response object has field "identifier" with "some_string"
        And Content Type Group with identifier "some_string" was created

    Scenario: Can't create a Content Type Group with same identifier of an existing group
        Given I have "administrator" permissions
        And there is a Content Type Group with identifier "some_string"
        When I create a Content Type Group with identifier "some_string"
        Then response has an invalid field error
        And only 1 Content Type Group with identifier "some_string" exists

    Scenario: Can't create a Content Type Group without authorized user
        Given I don't have permissions
        And there isn't a Content Type Group with identifier "some_string"
        When I create a Content Type Group with identifier "some_string"
        Then response has a not authorized error
        And Content Type Group with identifier "some_string" wasn't created
