Feature: ezpublish/console
    Scenario: Commands use the default siteaccess if not specified
        When I run a console script without specifying a siteaccess
        Then I expect it to be executed with the default siteaccess

    Scenario: Commands use the siteaccess specified as with --siteaccess
        When I run a console script with the siteaccess option "mobile"
        Then I expect it to be executed with the siteaccess "mobile"
