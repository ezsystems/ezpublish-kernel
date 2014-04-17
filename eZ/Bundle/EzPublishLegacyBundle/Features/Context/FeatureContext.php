<?php
/**
 * File containing the SetupWizardContext class.
 *
 * This class contains specific setup wizard feature context for Behat.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Features\Context;

use EzSystems\BehatBundle\Features\Context\Browser\BrowserContext;
use PHPUnit_Framework_Assert as Assertion;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;

/**
 * Setup Wizard context.
 */
class FeatureContext extends BrowserContext
{
    /**
     * @var array This var should have the association between title in setup and package
     */
    protected $packages = array(
        'ez publish demo site' => 'ezdemo_site',
        'ez publish demo site (without demo content)' => 'ezdemo_site_clean'
    );

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct( array $parameters )
    {
        parent::__construct( $parameters );

        $this->pageIdentifierMap += array(
            "Setup Wizard" => "/ezsetup",
        );
    }

    /**
     * @Given /^(?:|I )am (?:at|on) (?:|the )"([^"]*)" step/
     * @Then /^I see "([^"]*)" step$/
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

        $importElement = $this->findElmentAfterElement(
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
        $packages = $this->convertTableToArrayOfData( $packagesTable );

        foreach ( $packages as $packageName )
        {
            // this can't use the self::iSeeImported() since the versions don't
            // have a space between "ver." and the actual version
            $versionLabel = "$packageName (ver.{$version})";

            // notice this xpath uses contains instead of the "=" because the
            // text as an <enter> and trailling spaces, so it fails
            $el = $this->getSession()->getPage()->find(
                "xpath",
                "//*[contains( text(), '$versionLabel' )]"
            );

            Assertion::assertNotNull( $el, "Couldn't find '$versionLabel' package" );

            $importElement = $this->findElmentAfterElement(
                $this->findRow( $el )->findAll( "xpath", "td" ),
                "../*[contains( text(), '$versionLabel' )]",
                "//*[text() = 'Imported']"
            );

            Assertion::assertNotNull( $importElement, "Couldn't find 'Imported' for '$versionLabel' package" );
        }
    }
}
