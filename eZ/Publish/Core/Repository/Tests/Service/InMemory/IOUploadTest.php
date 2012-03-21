<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\InMemory\IOUploadTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\InMemory;

use PHPUnit_Extensions_PhptTestCase,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler;

/**
 * Test case for IO file upload using InMemory storage class
 */
class IOUploadTest extends PHPUnit_Extensions_PhptTestCase
{
    public function __construct()
    {
        parent::__construct( __DIR__ . '/upload.phpt' );
    }

    protected function getRepository( array $serviceSettings )
    {
        return new Repository( new InMemoryPersistenceHandler(), new InMemoryIOHandler(), $serviceSettings );
    }
}
