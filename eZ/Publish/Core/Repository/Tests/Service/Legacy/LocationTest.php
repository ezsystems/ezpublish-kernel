<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Legacy\LocationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Legacy;
use eZ\Publish\Core\Repository\Tests\Service\LocationBase as BaseLocationServiceTest;

/**
 * Test case for Location Service using Legacy storage class
 *
 */
class LocationTest extends BaseLocationServiceTest
{
    protected function getRepository()
    {
        try
        {
            return include 'common.php';
        }
        catch ( \Exception $e )
        {
            $this->markTestIncomplete(  $e->getMessage() );
        }
    }
}
