Feature: Login into administration
    As a administrator user
    I need to login
    In order to administrate and manage site

    Scenario: Perform login
        Given I am on "login" page
        When I fill in "Username" with "admin"
        And I fill in "Password" with "publish"
        And I click at "Log in" button
        Then I should be at homepage
