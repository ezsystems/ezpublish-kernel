@contentTypeGroup @adminFeature
Feature: Read a Content Type Groups
    As a developer
    I need to be able to read a Content Type Group
    In order to know use it

    # Get Content Type Group through <ID>
    Scenario: Read a Content Type Group through id
        Given I have "administrator" permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        When I create a "GET" request to "/content/typegroups/{id}"
        And I add "accept" header with a "ContentTypeGroup"
        And I send the request
        Then response contains Content Type Group with identifier "some_string"
        And I see response body with "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object

    Scenario: Get relevant information when attempting to read a non existing id
        Given I have "administrator" permissions
        And there isn't a Content Type Group with id "{id}"
        When I read Content Type Group with id "{id}"
        Then I see a not found exception

    Scenario: Can't read a Content Type Group without an authorized user through id
        Given I do not have permissions
        And there is a Content Type Group with id "{id}" and identifier "some_string"
        When I read Content Type Group with id "{id}"
        Then I see an unauthorized error

    # Get Content Type Group through <identifier>
    Scenario: Read a Content Type Group through identifier
        Given I have "administrator" permissions
        And there is a Content Type Group with identifier "some_string"
        When I create a "GET" request to "/content/typegroups?identifier=some_string"
        And I add "accept" header with a "ContentTypeGroup"
        And I send the request
        Then I see 200 status code
        And I see "OK" status message
        And response contains Content Type Group with identifier "some_string"
        And I see response body with "eZ\Publish\Core\REST\Client\Values\ContentType\ContentTypeGroup" object

    Scenario: Get relevant information when attempting to read a non existing identifier
        Given I have "administrator" permissions
        And there isn't a Content Type Group with identifier "some_string"
        When I read Content Type Group with identifier "some_string"
        Then I see a not found exception

    Scenario: Can't read a Content Type Group without an authorized user through identifier
        Given I do not have permissions
        And there is a Content Type Group with identifier "some_string"
        When I read Content Type Group with identifier "some_string"
        Then I see an unauthorized error

