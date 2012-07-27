<?php
/**
 * File containing the IOServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the IOService using legacy storage.
 *
 * @see eZ\Publish\API\Repository\IOService
 * @group io
 */
class IOServiceLegacyTest extends IOServiceTest
{
    protected function getRepository()
    {
        $this->markTestIncomplete( 'Core repository implementation does not have the setLegacyKernel() method.' );

        if ( !isset( $_ENV['legacyKernel'] ) )
            self::markTestSkipped( 'Legacy kernel is needed to run this test.' );

        $repository = parent::getRepository();
        $repository->setLegacyKernel( $_ENV['legacyKernel'] );
        return $repository;
    }
}
