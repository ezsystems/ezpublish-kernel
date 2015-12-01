Feature: app/console
    Scenario: Commands use the default siteaccess if not specified
        When I run a console script without specifying a siteaccess
        Then it is executed with the default one

    Scenario: Commands use the siteaccess specified as with --siteaccess
        Given that there is a siteaccess that is not the default one
         When I run a console script with it
         Then I expect it to be executed with it
