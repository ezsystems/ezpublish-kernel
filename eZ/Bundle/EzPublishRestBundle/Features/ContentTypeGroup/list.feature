@contentTypeGroup @adminFeature
Feature: Read all Content Type Groups
    As a developer
    I need to list all exiting Content Type Groups
    In order to have an over view on Content Type Groups

    Scenario: Read all Content Type Groups
        Given I have "administrator" permissions
        And there are the following Content Type Groups:
            | groups     |
            | some       |
            | dif3rent   |
            | id_nt.fier |
        When I create a "GET" request to "/content/typegroups"
        And I add "accept" header to "List" a "ContentTypeGroup"
        And I send the request
        Then I see 200 status code
        And I see "OK" status message
        And I see the following Content Type Groups:
            | groups     |
            | some       |
            | dif3rent   |
            | id_nt.fier |

    Scenario: Can't read any Content Type Group without an authorized user
        Given I do not have permissions
        When I read Content Type Groups list
        Then I see an unauthorized error
