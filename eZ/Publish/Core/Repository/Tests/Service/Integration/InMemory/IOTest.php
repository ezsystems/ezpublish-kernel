<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory\IOTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration\InMemory;

use eZ\Publish\Core\Repository\Tests\Service\Integration\IOBase as BaseIOServiceTest;

/**
 * Test case for IO Service using InMemory storage class
 */
class IOTest extends BaseIOServiceTest
{
    /**
     * @return \PHPUnit_Extensions_PhptTestCase
     */
    protected function getFileUploadTest()
    {
        return new IOUploadPHPT();
    }

    protected function getRepository()
    {
        return Utils::getRepository();
    }
}
