Feature: Install eZ Publish Demo with/withoutout content
    In order to install eZ Publish Demo
    As an anonymous user
    I need to be able to install eZ Publish Demo through Setup Wizard

    @javascript @democontent_install @democlean_install
    Scenario: Choose english UK for installation
        Given I am on the "Setup Wizard" page
        And I am on "Welcome to eZ Publish Community Project 5.3.0alpha1" step
        When I select "English (United Kingdom)"
        And I press "Next"
        Then I see "Outgoing Email" step

    @javascript @democontent_install @democlean_install
    Scenario: Choose Sendmail/MTA
        Given I am on "Outgoing Email" step
        When I select "Sendmail/MTA" radio button
        And I press "Next"
        Then I see "Database initialization" step

    @javascript @democontent_install @democlean_install
    Scenario: Setup database connection
        Given I am on "Database initialization" step
        When I fill form with:
            | field      | value     |
            | Servername | localhost |
            | Port       |           |
            | Username   | ezp       |
            | Password   | ezp       |
        And I press "Next"
        Then I see "Language support" step

    @javascript @democontent_install @democlean_install
    Scenario: Choose English UK and German as languages for installation
        Given I am on "Language support" step
        When I select "English (United Kingdom)" radio button
        And I check "German" checkbox
        And I press "Next"
        Then I see "Site package" step

    @javascript @democontent_install
    Scenario: Choose Demo Site (with content) for installation
        Given I am on "Site package" step
        When I select "eZ Publish Demo Site" package version "5.3.0alpha1"
        And I press "Next"
        Then I see "Site package" step
        And I see "eZ Publish Demo Site" package version "5.3-0-alpha1" imported
        And I see following packages for version "5.3.0-alpha1" imported:
            | package                   |
            | ezwt_extension            |
            | ezstarrating_extension    |
            | ezgmaplocation_extension  |
            | ezdemo_extension          |
            | ezflow_extension          |
            | ezcomments_extension      |
            | ezdemo_classes            |
            | ezdemo_democontent        |
        And I don't see "Not Imported" message

    @javascript @democlean_install
    Scenario: Choose Demo Site (with content) for installation
        Given I am on "Site package" step
        When I select "eZ Publish Demo Site (without demo content)" package version "5.3.0alpha1"
        And I press "Next"
        Then I see "Site package" step
        And I see "eZ Publish Demo Site (without demo content)" package version "5.3-0-alpha1" imported
        And I see following packages for version "5.3.0-alpha1" imported:
            | package                   |
            | ezwt_extension            |
            | ezstarrating_extension    |
            | ezgmaplocation_extension  |
            | ezdemo_extension          |
            | ezflow_extension          |
            | ezcomments_extension      |
            | ezdemo_classes            |
            | ezdemo_democontent_clean  |
        And I don't see "Not Imported" message

    @javascript @democontent_install @democlean_install
    Scenario: See that all was successfully imported
        Given I am on "Site package" step
        When I click at "Next" button
        Then I see "Site access configuration" step

    @javascript @democontent_install @democlean_install
    Scenario: Choose the recommended URL site access configuration
        Given I am on "Site access configuration" step
        When I select "URL" radio button
        And I press "Next"
        Then I see "Site details" step

    @javascript @democontent_install
    Scenario: Define site details
        Given I am on "Site details" step
        When I fill form with:
            | field         | value                             |
            | Title         | eZ Publish Demo Site With Content |
            | Site url      | http://localhost                  |
            | User path     | behat_site                        |
            | Admin path    | behat_site_admin                  |
        And I select "behattestdb"
        And I press "Next"
        Then I see "Site administrator" step

    @javascript @democlean_install
    Scenario: Define site details
        Given I am on "Site details" step
        When I fill form with:
            | field         | value                                |
            | Title         | eZ Publish Demo Site Without Content |
            | Site url      | http://localhost                     |
            | User path     | behat_site                           |
            | Admin path    | behat_site_admin                     |
        And I select "behattestdb"
        And I press "Next"
        Then I see "Site administrator" step

    @javascript @democontent_install @democlean_install
    Scenario: Define master administrator user
        Given I am on "Site administrator" step
        When I fill form with:
            | field             | value             |
            | First name        | Admin             |
            | Last name         | User              |
            | Email address     | foo@example.com   |
            | Password          | publish           |
            | Confirm password  | publish           |
        And I press "Next"
        Then I see "Site registration" step

    @javascript @democontent_install @democlean_install
    Scenario: Define the data for the information email
        Given I am on "Site registration" step
        When I fill form with:
            | field        | value          |
            | First name   | Testing        |
            | Last name    | Installation   |
            | Your email   | nospam@ez.no   |
            | Country      | Norway         |
            | Company      | eZ Systems     |
        And I press "Next"
        Then I see "Finished" step
