@contentTypeGroup
Feature: Delete a Content Type Group
    As a developer
    I need to delete a Content Type Group
    In order to remove old or not needed groups

    Scenario: Delete the Content Type Group
        Given I have "administrator" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        When I send a "DELETE" request to "/content/typegroups/{id}"
        Then Content Type Group with identifier "some_string" was deleted

    Scenario: Get a informative error when deleting a non existent Content Type Group
        Given I have "administrator" permissions
        And there isn't a Content Type Group with id "{id}"
        When I send a "DELETE" request to "/content/typegroups/{id}"
        Then response has a not found exception

    Scenario: Can't delete a Content Type Group without an authorized user
        Given I don't have permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        When I send a "DELETE" request to "/content/typegroups/{id}"
        Then response has a not authorized exception
        And Content Type Group with identifier "some_string" wasn't deleted

    # this needs to be updated when it's possible to create ContentTypes through BDD
    Scenario: Can't delete a Content Type Group that isn't empty
        Given I have "administrator" permissions
        When I send a "DELETE" request to "/content/typegroups/1"
        Then response has a forbidden exception with message "Only empty content type groups can be deleted"
