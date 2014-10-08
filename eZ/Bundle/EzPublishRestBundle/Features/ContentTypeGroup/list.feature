@contentTypeGroup
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
        And I set header "accept" for "ContentTypeGroupList" object
        And I send the request
        Then response status code is 200
        And response status message is "OK"
        And response contains the following Content Type Groups:
            | groups     |
            | some       |
            | dif3rent   |
            | id_nt.fier |

    Scenario: Can't read any Content Type Group without an authorized user
        Given I don't have permissions
        When I get Content Type Groups list
        Then response has a not authorized error
