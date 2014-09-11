<?php
/**
 * File containing the SetupWizard Context class for the LegacyBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Features\Context\SetupWizard;

use EzSystems\BehatBundle\Helper\Gherkin as GherkinHelper;
use eZ\Bundle\EzPublishLegacyBundle\Features\Context\Legacy;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assertion;

class Context extends Legacy
{
    /**
     * @var array This var should have the association between title in setup and package
     */
    protected $packages = array();

    /**
     * Initialize parameters
     */
    public function __construct()
    {
        parent::__construct();

        $this->pageIdentifierMap += array(
            "setup wizard" => "/ezsetup",
        );

        $this->packages += array(
            'ez publish demo site' => 'ezdemo_site',
            'ez publish demo site (without demo content)' => 'ezdemo_site_clean'
        );
    }

    /**
     * Enables the possibility to run all the setup wizard features without the
     * need to make a clean installation for each
     *
     * @AfterSuite
     */
    static function clearInstallation()
    {
        // @todo: implementation
    }

    /**
     * @Given I am at/on (the) ":stepTitle step
     * @Then  I see :stepTitle step
     */
    public function iAmOnStep( $stepTitle )
    {
        $this->iShouldBeOnPage( 'Setup Wizard' );
        $this->iSeeTitle( $stepTitle );
    }

    /**
     * @When I select :packageName package version :version
     */
    public function iSelectPackage( $packageName, $version )
    {
        $package = $this->packages[strtolower( $packageName )];
        Assertion::assertNotNull( $package, "Package '$packageName' not defined" );

        // first select the package
        $fields = $this->getXpath()->findFields( $package );
        Assertion::assertNotEmpty( $fields, "Couldn't find '$package' field" );
        $fields[0]->check();

        // now verify version
        // IMPORTANT: only verify the values shown on the page, does not actually
        //      verify if the package is in a given version
        $versionLabel = "$packageName (ver. $version)";
        $this->iSeeTitle( $versionLabel );
    }

    /**
     * @Then I see :packageName package version :version imported
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
     * @Then I see following packages for version :version imported:
     */
    public function iSeeFollowingPackagesForVersionImported( $version, TableNode $packagesTable )
    {
        $packages = GherkinHelper::convertTableToArrayOfData( $packagesTable );

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

    /**
     * Simple override for the following sentences:
     * - Given I filled form with:
     * - When I fill form with:
     */
    public function iFillFormWith( TableNode $table )
    {
        foreach ( GherkinHelper::convertTableToArrayOfData( $table ) as $field => $value )
        {
            // fill the form
            $el = $this->findFieldElement( $field );
            $el->setValue( $value );
        }
    }

    /**
    * Find field element
    * This is a complement to the normal search, because in some cases the
    * label has no "for" attribute, so the normal search won't find it. So this
    * will try to find an input right after a label with $field
    *
    * @param string $field Can be id, name, label, value
    *
    * @return \Behat\Mink\Element\NodeElement
    */
    protected function findFieldElement( $field )
    {
        $page = $this->getSession()->getPage();
        // attempt to find field through id, name, or label
        $fieldElement = $page->findField( $field );
        if ( empty( $fieldElement ) )
        {
            // if field wasn't found, and there is an label for that, we will
            // attempt to find the next input after the label
            $fieldElement = $page->find(
                "xpath",
                "//*[self::" .
                implode( " or self::", $this->getTagsFor( 'input' ) )
                . "][preceding::label[contains( text(), "
                . $this->getXpath()->literal( $field )
                . " )]]"
            );
        }
        Assertion::assertNotNull( $fieldElement, "Couldn't find '$field' field" );
        return $fieldElement;
    }
}
