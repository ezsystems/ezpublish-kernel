<?php
/**
 * File containing the Test Setup Factory base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\SetupFactory;
use eZ\Publish\API\Repository\Tests\SetupFactory;
use eZ\Publish\API\Repository\Tests\IdManager;
use eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class Legacy extends SetupFactory
{
    /**
     * Returns a configured repository for testing.
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        return require __DIR__ . '/../../../../Core/Repository/Tests/Service/Legacy/common.php';
    }

    /**
     * Returns a repository specific ID manager.
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    public function getIdManager()
    {
        return new IdManager\Php;
    }
}
