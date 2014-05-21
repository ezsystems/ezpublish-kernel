<?php
/**
 * File containing the SetupWizardContext class for the LegacyBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Features\Context;

use PHPUnit_Framework_Assert as Assertion;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;

class SetupWizardContext extends LegacyContext
{
    /**
     * @var array This var should have the association between title in setup and package
     */
    protected $packages = array();

    public function __construct( $parameters )
    {
        parent::__construct( $parameters );

        $this->pageIdentifierMap += array(
            "setup wizard" => "/ezsetup",
        );

        $this->packages += array(
            'ez publish demo site' => 'ezdemo_site',
            'ez publish demo site (without demo content)' => 'ezdemo_site_clean'
        );
    }

    /**
     * @Given /^I am (?:at|on) (?:|the )"(?P<stepTitle>[^"]*)" step/
     * @Then /^I see "(?P<stepTitle>[^"]*)" step$/
     */
    public function iAmOnStep( $stepTitle )
    {
        return array(
            new Step\Then( 'I see "Setup Wizard" page' ),
            new Step\Then( 'I see "' . $stepTitle . '" title' ),
        );
    }

    /**
     * @When /^I select "([^"]*)" package version "([^"]*)"$/
     */
    public function iSelectPackage( $packageName, $version )
    {
        $package = $this->packages[strtolower( $packageName )];
        Assertion::assertNotNull( $package, "Package '$packageName' not defined" );

        // first select the package
        $field = $this->getSession()->getPage()->findField( $package );
        Assertion::assertNotNull( $field, "Couldn't find '$package' field" );
        $this->browserFillField( $field );

        // now verify version
        // IMPORTANT: only verify the values shown on the page, does not actually
        //      verify if the package is in a given version
        $versionLabel = "$packageName (ver. $version)";
        return array(
            new Step\Then( 'I see "' . $versionLabel . '" title' )
        );
    }

    /**
     * @Then /^I see "([^"]*)" package version "([^"]*)" imported$/
     */
    public function iSeeImported( $packageName, $version )
    {
        $versionLabel = "$packageName (ver. $version)";
        $packageXpath = "//h2[text() = '$versionLabel']";
        $el = $this->getSession()->getPage()->find(
            "xpath",
            $packageXpath
        );

        Assertion::assertNotNull( $el, "Couldn't find '$versionLabel' package" );

        $importElement = $this->findElementAfterElement(
            $this->findRow( $el )->findAll( "xpath", "td" ),
            $packageXpath,
            "//*[text() = 'Imported']"
        );

        Assertion::assertNotNull( $importElement, "Couldn't find 'Imported' for '$versionLabel' package" );
    }

    /**
     * @Then /^I see following packages for version "([^"]*)" imported(?:|\:)$/
     */
    public function iSeeFollowingPackagesForVersionImported( $version, TableNode $packagesTable )
    {
        $packages = $this->getSubContext( 'Common' )->convertTableToArrayOfData( $packagesTable );

        foreach ( $packages as $packageName )
        {
            // this can't use the self::iSeeImported() since the versions don't
            // have a space between "ver." and the actual version
            $versionLabel = "$packageName (ver.{$version})";

            // notice this xpath uses contains instead of the "=" because the
            // text as an <enter> and trailing spaces, so it fails
            $el = $this->getSession()->getPage()->find(
                "xpath",
                "//*[contains( text(), '$versionLabel' )]"
            );

            Assertion::assertNotNull( $el, "Couldn't find '$versionLabel' package" );

            $importElement = $this->findElementAfterElement(
                $this->findRow( $el )->findAll( "xpath", "td" ),
                "../*[contains( text(), '$versionLabel' )]",
                "//*[text() = 'Imported']"
            );

            Assertion::assertNotNull( $importElement, "Couldn't find 'Imported' for '$versionLabel' package" );
        }
    }
}
