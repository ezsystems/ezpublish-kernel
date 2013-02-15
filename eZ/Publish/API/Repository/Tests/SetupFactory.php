<?php
/**
 * File containing the Test Setup Factory base class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
abstract class SetupFactory
{
    /**
     * Returns a configured repository for testing.
     *
     * @param boolean $initializeFromScratch if the back end should be initialized
     *                                    from scratch or re-used
     * @return \eZ\Publish\API\Repository\Repository
     */
    abstract public function getRepository( $initializeFromScratch = true );

    /**
     * Returns a repository specific ID manager.
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    abstract public function getIdManager();

    /**
     * Returns a config value for $configKey.
     *
     * @param string $configKey
     *
     * @throws Exception if $configKey could not be found.
     *
     * @return mixed
     */
    abstract public function getConfigValue( $configKey );
}
