@contentTypeGroup
Feature: Update a Content Type Group
    As a developer
    I need to be able to read a Content Type Group
    In order to fix some mistake

    Scenario: Update the Content Type Group identifier
        Given I have "administrator" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        And there isn't a Content Type Group with identifier "another_string"
        When I create a "PATCH" request to "/content/typegroups/{id}"
        And I set header "content-type" with "ContentTypeGroupInput" object
        And I set header "accept" for "ContentTypeGroup" object
        And I make a "ContentTypeGroupUpdateStruct" object
        And I set field "identifier" to "another_string"
        And I send the request
        Then Content Type Group with identifier "another_string" exists
        And Content Type Group with identifier "some_string" doesn't exist anymore

    Scenario: Get relevant information when updating a Content Type Group
        Given I have "administrator" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        And there isn't a Content Type Group with identifier "another_string"
        When I update Content Type Group with identifier "some_string" to "another_string"
        Then response status code is 200
        And response status message is "OK"
        And Content Type Group with identifier "another_string" exists
        And Content Type Group with identifier "some_string" doesn't exist anymore
        And response has a "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object

    Scenario: Can't update the Content Type Group identifier to an existing one
        Given I have "administrator" permissions
        And there are the following Content Type Groups:
            | groups         |
            | some_string    |
            | another_string |
        When I update Content Type Group with identifier "some_string" to "another_string"
        Then response has an invalid field error
        And Content Type Group with identifier "some_string" exists
        And Content Type Group with identifier "another_string" exists

    Scenario: Can't update a Content Type Group
        Given I do not have permissions
        And there is a Content Type Group with identifier "some_string"
        And there isn't a Content Type Group with identifier "another_string"
        When I update Content Type Group with identifier "some_string" to "another_string"
        Then response has a not authorized error
        And Content Type Group with identifier "some_string" exists
        And Content Type Group with identifier "another_string" doesn't exist
