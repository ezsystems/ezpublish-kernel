<?php
/**
 * File containing the LocationServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;

/**
 * Test case for operations in the LocationService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LocationService
 */
class LocationServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::createLocation() is not implemented." );
    }

    /**
     * Test for the loadLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::loadLocation() is not implemented." );
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadLocationByRemoteIdThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::loadLocationByRemoteId() is not implemented." );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::updateLocation() is not implemented." );
    }

    /**
     * Test for the loadMainLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadMainLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadMainLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::loadMainLocation() is not implemented." );
    }

    /**
     * Test for the swapLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::swapLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testSwapLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::swapLocation() is not implemented." );
    }

    /**
     * Test for the hideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::hideLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testHideLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::hideLocation() is not implemented." );
    }

    /**
     * Test for the unhideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::unhideLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnhideLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::unhideLocation() is not implemented." );
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteLocationThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::deleteLocation() is not implemented." );
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopySubtreeThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::copySubtree() is not implemented." );
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testMoveSubtreeThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::moveSubtree() is not implemented." );
    }
}
