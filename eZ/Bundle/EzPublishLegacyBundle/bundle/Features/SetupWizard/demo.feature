@javascript @demo
Feature: Install eZ Publish Demo with/without content
    As an anonymous user
    I need to be able to install eZ Publish Demo through Setup Wizard
    In order to interact with eZ Demo installation

    Scenario: Choose english UK as setup wizard language
        Given I am on the "Setup Wizard" page
        And I am on "Welcome to eZ Publish Community Project 5.90.0alpha1" step
        When I select "English (United Kingdom)"
        And I click at "Next" button
        Then I see "Outgoing Email" step

    @uniqueDatabaseSystem
    Scenario: Choose Sendmail/MTA
        Given I am on "Outgoing Email" step
        When I select "Sendmail/MTA" radio button
        And I click at "Next" button
        Then I see "Database initialization" step

    @nonUniqueDatabaseSystem @skipByDefault
    Scenario: Choose Sendmail/MTA
        Given I am on "Outgoing Email" step
        When I select "Sendmail/MTA" radio button
        And I click at "Next" button
        Then I see "Choose database system" step

    @nonUniqueDatabaseSystem @skipByDefault
    Scenario: Choose which database system to use
        Given I am on "Choose database system" step
        When I select "MySQL Improved" radio button
        And I click at "Next" button
        Then I see "Database initialization" step

    Scenario: Setup database connection
        Given I am on "Database initialization" step
        When I fill form with:
            | field      | value     |
            | Servername | localhost |
            | Port       |           |
            | Username   | ezp       |
            | Password   | ezp       |
        And I click at "Next" button
        Then I see "Language support" step

    Scenario: Choose English UK and German as languages for installation
        Given I am on "Language support" step
        When I select "English (United Kingdom)" radio button
        And I check "German" checkbox
        And I click at "Next" button
        Then I see "Site package" step

    @content
    Scenario: Choose Demo Site (with content) for installation
        Given I am on "Site package" step
        When I select "eZ Publish Demo Site" package version "5.4.0"
        And I click at "Next" button
        Then I see "Site package" step
        And I see "eZ Publish Demo Site" package version "5.4-0" imported
        And I see following packages for version "5.3.0" imported:
            | package                   |
            | ezwt_extension            |
            | ezstarrating_extension    |
            | ezgmaplocation_extension  |
            | ezflow_extension          |
        And I see following packages for version "5.4.0" imported:
            | package                   |
            | ezdemo_extension          |
            | ezdemo_classes            |
            | ezdemo_democontent        |
        And I don't see "Not Imported" message

    @clean
    Scenario: Choose Demo Site (without content) for installation
        Given I am on "Site package" step
        When I select "eZ Publish Demo Site (without demo content)" package version "5.4.0"
        And I click at "Next" button
        Then I see "Site package" step
        And I see "eZ Publish Demo Site (without demo content)" package version "5.4-0" imported
        And I see following packages for version "5.3.0" imported:
            | package                   |
            | ezwt_extension            |
            | ezstarrating_extension    |
            | ezgmaplocation_extension  |
            | ezflow_extension          |
        And I see following packages for version "5.4.0" imported:
            | package                   |
            | ezdemo_extension          |
            | ezdemo_classes            |
            | ezdemo_democontent_clean  |
        And I don't see "Not Imported" message

    Scenario: See that all was successfully imported
        Given I am on "Site package" step
        When I click at "Next" button
        Then I see "Site access configuration" step

    Scenario: Choose the recommended URL site access configuration
        Given I am on "Site access configuration" step
        When I select "URL" radio button
        And I click at "Next" button
        Then I see "Site details" step

    @content
    Scenario: Define site details
        Given I am on "Site details" step
        When I fill form with:
            | field         | value                             |
            | Title         | eZ Publish Demo Site With Content |
            | Site url      | http://localhost                  |
            | User path     | behat_site                        |
            | Admin path    | behat_site_admin                  |
        And I select "behattestdb"
        And I click at "Next" button
        Then I see "Site administrator" step

    @clean
    Scenario: Define site details
        Given I am on "Site details" step
        When I fill form with:
            | field         | value                                |
            | Title         | eZ Publish Demo Site Without Content |
            | Site url      | http://localhost                     |
            | User path     | behat_site                           |
            | Admin path    | behat_site_admin                     |
        And I select "behattestdb"
        And I click at "Next" button
        Then I see "Site administrator" step

    # @todo: Make the non empty DB step

    Scenario: Define master administrator user
        Given I am on "Site administrator" step
        When I fill form with:
            | field             | value             |
            | First name        | Admin             |
            | Last name         | User              |
            | Email address     | foo@example.com   |
            | Password          | publish           |
            | Confirm password  | publish           |
        And I click at "Next" button
        Then I see "Open source software is nothing without a vibrant community!" step

    Scenario: Show open source information
        Given I am on "Open source software is nothing without a vibrant community!" step
        When I click at "Next" button
        Then I see "Finished" step
