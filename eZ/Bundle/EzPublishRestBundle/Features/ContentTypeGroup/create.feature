@contentTypeGroup @adminFeature
Feature: Create Content Type Group
    As a developer
    I need to create a Content Type Group
    In order to make a group for Content Types

    Scenario: Create a valid Content Type Group
        Given I have "administrator" permissions
        And there isn't a Content Type Group with identifier "some_string"
        When I create a "POST" request to "/content/typegroups"
        And I add "content-type" header with "Input" for "ContentTypeGroup"
        And I add "accept" header for a "ContentTypeGroup"
        And I make a "ContentTypeGroupCreateStruct" object
        And I add "some_string" value to "identifier" field
        And I send the request
        Then Content Type Group with identifier "some_string" is stored
        And response contains Content Type Group with identifier "some_string"

    Scenario: Get relevant information after creating the Content Type Group
        Given I have "administrator" permissions
        And there isn't a Content Type Group with identifier "some_string"
        When I create a Content Type Group with identifier "some_string"
        Then Content Type Group with identifier "some_string" is stored
        And I see 201 status code
        And I see "Created" status message
        And I see "content-type" header with a "ContentTypeGroup"
        And I see response body with "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object
        And I see response object field "identifier" with "some_string" value

    Scenario: Can't create a Content Type Group with same identifier of an existing group
        Given I have "administrator" permissions
        And there is a Content Type Group with identifier "some_string"
        When I create a Content Type Group with identifier "some_string"
        Then I see an invalid field error
        And only 1 Content Type Group with identifier "some_string" is stored

    Scenario: Can't create a Content Type Group without authorized user
        Given I do not have permissions
        And there isn't a Content Type Group with identifier "some_string"
        When I create a Content Type Group with identifier "some_string"
        Then I see not authorized error
        And Content Type Group with identifier "some_string" is not stored
