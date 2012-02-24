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
class LocationServiceTest extends BaseTest
{
    /**
     * Test for the newLocationCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct()
     * @dep_ends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testNewLocationCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $locationCreate = $locationService->newLocationCreateStruct(
            1
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationCreateStruct',
            $locationCreate
        );

        return $locationCreate;
    }

    /**
     * Test for the newLocationCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     */
    public function testNewLocationCreateStructValues( LocationCreateStruct $locationCreate )
    {
        $this->assertPropertiesCorrect(
            array(
                'priority'         => 0,
                'hidden'           => false,
                'remoteId'         => null,
                'isMainLocation'   => false,
                'sortField'        => Location::SORT_FIELD_NAME,
                'sortOrder'        => Location::SORT_ORDER_ASC,
                'parentLocationId' => 1,
            ),
            $locationCreate
        );
    }

    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * 
     */
    public function testCreateLocation()
    {
        $this->markTestIncomplete( "Test for LocationService::createLocation() is not implemented." );
    }

    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateLocationThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for LocationService::createLocation() is not implemented." );
    }

    /**
     * Test for the loadLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * 
     */
    public function testLoadLocation()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocation() is not implemented." );
    }

    /**
     * Test for the loadLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLocationThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocation() is not implemented." );
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * 
     */
    public function testLoadLocationByRemoteId()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocationByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLocationByRemoteIdThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocationByRemoteId() is not implemented." );
    }

    /**
     * Test for the newLocationUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::newLocationUpdateStruct()
     * 
     */
    public function testNewLocationUpdateStruct()
    {
        $this->markTestIncomplete( "Test for LocationService::newLocationUpdateStruct() is not implemented." );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * 
     */
    public function testUpdateLocation()
    {
        $this->markTestIncomplete( "Test for LocationService::updateLocation() is not implemented." );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testUpdateLocationThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for LocationService::updateLocation() is not implemented." );
    }

    /**
     * Test for the loadMainLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadMainLocation()
     * 
     */
    public function testLoadMainLocation()
    {
        $this->markTestIncomplete( "Test for LocationService::loadMainLocation() is not implemented." );
    }

    /**
     * Test for the loadMainLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadMainLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testLoadMainLocationThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for LocationService::loadMainLocation() is not implemented." );
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * 
     */
    public function testLoadLocations()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocations() is not implemented." );
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations($contentInfo, $rootLocation)
     * 
     */
    public function testLoadLocationsWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocations() is not implemented." );
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testLoadLocationsThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocations() is not implemented." );
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations($contentInfo, $rootLocation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testLoadLocationsThrowsBadStateExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocations() is not implemented." );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren()
     * 
     */
    public function testLoadLocationChildren()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocationChildren() is not implemented." );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset)
     * 
     */
    public function testLoadLocationChildrenWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocationChildren() is not implemented." );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset, $limit)
     * 
     */
    public function testLoadLocationChildrenWithThirdParameter()
    {
        $this->markTestIncomplete( "Test for LocationService::loadLocationChildren() is not implemented." );
    }

    /**
     * Test for the swapLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::swapLocation()
     * 
     */
    public function testSwapLocation()
    {
        $this->markTestIncomplete( "Test for LocationService::swapLocation() is not implemented." );
    }

    /**
     * Test for the hideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::hideLocation()
     * 
     */
    public function testHideLocation()
    {
        $this->markTestIncomplete( "Test for LocationService::hideLocation() is not implemented." );
    }

    /**
     * Test for the unhideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::unhideLocation()
     * 
     */
    public function testUnhideLocation()
    {
        $this->markTestIncomplete( "Test for LocationService::unhideLocation() is not implemented." );
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * 
     */
    public function testDeleteLocation()
    {
        $this->markTestIncomplete( "Test for LocationService::deleteLocation() is not implemented." );
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * 
     */
    public function testCopySubtree()
    {
        $this->markTestIncomplete( "Test for LocationService::copySubtree() is not implemented." );
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCopySubtreeThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for LocationService::copySubtree() is not implemented." );
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * 
     */
    public function testMoveSubtree()
    {
        $this->markTestIncomplete( "Test for LocationService::moveSubtree() is not implemented." );
    }
}
