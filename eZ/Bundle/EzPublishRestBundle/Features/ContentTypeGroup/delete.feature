@contentTypeGroup @adminFeature
Feature: Delete a Content Type Group
    As a developer
    I need to delete a Content Type Group
    In order to remove old or not needed groups

    Scenario: Delete the Content Type Group
        Given I have "administrator" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        When I send a "DELETE" request to "/content/typegroups/{id}"
        Then Content Type Group with identifier "some_string" is removed

    Scenario: Get a informative error when deleting a non existent Content Type Group
        Given I have "administrator" permissions
        And there isn't a Content Type Group with id "{id}"
        When I send a "DELETE" request to "/content/typegroups/{id}"
        Then I see a not found exception

    Scenario: Can't delete a Content Type Group without an authorized user
        Given I do not have permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        When I send a "DELETE" request to "/content/typegroups/{id}"
        Then I see an unauthorized exception
        And Content Type Group with identifier "some_string" was not removed

    Scenario: Can't delete a Content Type Group that isn't empty
        Given I have "administrator" permissions
        When I send a "DELETE" request to "/content/typegroups/1"
        Then I see a forbidden exception with "Only empty content type groups can be deleted" message
