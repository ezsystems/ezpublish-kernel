@contentTypeGroup @adminFeature
Feature: Update a Content Type Group
    As a developer
    I need to be able to read a Content Type Group
    In order to fix some mistake

    Scenario: Update the Content Type Group identifier
        Given I have "administrator" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        And there isn't a Content Type Group with identifier "another_string"
        When I create a "PATCH" request to "/content/typegroups/{id}"
        And I add "content-type" header with "Input" for "ContentTypeGroup"
        And I add "accept" header for a "ContentTypeGroup"
        And I make a "ContentTypeGroupUpdateStruct" object
        And I add "another_string" value to "identifier" field
        And I send the request
        Then Content Type Group with identifier "another_string" was stored
        And Content Type Group with identifier "some_string" was removed

    Scenario: Get relevant information when updating a Content Type Group
        Given I have "administrator" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        And there isn't a Content Type Group with identifier "another_string"
        When I update Content Type Group with identifier "some_string" to "another_string"
        Then I see 200 status code
        And I see "OK" status message
        And Content Type Group with identifier "another_string" was stored
        And Content Type Group with identifier "some_string" was removed
        And I see response body with "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object

    Scenario: Can't update the Content Type Group identifier to an existing one
        Given I have "administrator" permissions
        And there are the following Content Type Groups:
            | groups         |
            | some_string    |
            | another_string |
        When I update Content Type Group with identifier "some_string" to "another_string"
        Then I see an invalid field error
        And Content Type Group with identifier "some_string" wasn't removed
        And Content Type Group with identifier "another_string" wasn't removed

    Scenario: Can't update a Content Type Group
        Given I do not have permissions
        And there is a Content Type Group with identifier "some_string"
        And there isn't a Content Type Group with identifier "another_string"
        When I update Content Type Group with identifier "some_string" to "another_string"
        Then I see an unauthorized error
        And Content Type Group with identifier "some_string" wasn't removed
        And Content Type Group with identifier "another_string" wasn't stored
