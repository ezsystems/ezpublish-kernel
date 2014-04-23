<?php
/**
 * File containing the LegacyInternalSentences class.
 *
 * This interface contains the legacy internal sentences that will match some
 * action or assertion only for legacy testing
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Features\Context;

use Behat\Gherkin\Node\TableNode;

/**
 * Interface LegacyInternalSentences
 */
interface LegacyInternalSentences
{

    /**
     * @Given /^I am (?:at|on) (?:|the )"(?P<stepTitle>[^"]*)" step/
     * @Then /^I see "(?P<stepTitle>[^"]*)" step$/
     */
    public function iAmOnStep( $stepTitle );

    /**
     * @When /^I select "([^"]*)" package version "([^"]*)"$/
     */
    public function iSelectPackage( $packageName, $version );

    /**
     * @Then /^I see "([^"]*)" package version "([^"]*)" imported$/
     */
    public function iSeeImported( $packageName, $version );

    /**
     * @Then /^I see following packages for version "([^"]*)" imported(?:|\:)$/
     */
    public function iSeeFollowingPackagesForVersionImported( $version, TableNode $packagesTable );
}
