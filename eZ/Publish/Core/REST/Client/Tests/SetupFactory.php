<?php
/**
 * File containing the Test Setup Factory for the REST SDK
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Tests;
use eZ\Publish\API\REST\Common;
use eZ\Publish\API\Repository;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class SetupFactory extends Repository\Tests\SetupFactory
{
    /**
     * Returns a configured repository for testing.
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        return require __DIR__ . '/../../common.php';
    }

    /**
     * Returns a repository specific ID manager.
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    public function getIdManager()
    {
        return new IdManager(
            new Common\UrlHandler\eZPublish()
        );
    }
}
