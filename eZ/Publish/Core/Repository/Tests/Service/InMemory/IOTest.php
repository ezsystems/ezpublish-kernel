<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\InMemory\IOTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\InMemory;
use eZ\Publish\Core\Repository\Tests\Service\IOBase as BaseIOServiceTest,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\Core\IO\InMemoryHandler as InMemoryIOHandler,
    eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryPersistenceHandler,

    eZ\Publish\Core\Repository\Tests\Service\InMemory\IOUploadTest;

/**
 * Test case for IO Service using InMemory storage class
 */
class IOTest extends BaseIOServiceTest
{
    public function __construct()
    {
        $this->fileUploadTest = new IOUploadTest();
    }

    protected function getRepository( array $serviceSettings )
    {
        return new Repository( new InMemoryPersistenceHandler(), new InMemoryIOHandler(), $serviceSettings );
    }
}
