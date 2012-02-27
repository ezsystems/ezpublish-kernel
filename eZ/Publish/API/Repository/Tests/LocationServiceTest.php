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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo( 108 );

        $locationCreate = $locationService->newLocationCreateStruct( 5 );
        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = 'sindelfingen';
        $locationCreate->isMainLocation = true;
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        $location = $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $location
        );

        return array(
            'locationCreate'  => $locationCreate,
            'createdLocation' => $location,
            'contentInfo'     => $contentInfo,
            'parentLocation'  => $locationService->loadLocation( 5 ),
        );
    }

    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testCreateLocationStructValues( array $data )
    {
        $locationCreate  = $data['locationCreate'];
        $createdLocation = $data['createdLocation'];
        $contentInfo     = $data['contentInfo'];

        $this->assertPropertiesCorrect(
            array(
                'priority'                => $locationCreate->priority,
                'hidden'                  => $locationCreate->hidden,
                'invisible'               => null,
                'remoteId'                => $locationCreate->remoteId,
                'contentInfo'             => $contentInfo,
                'parentLocationId'        => $locationCreate->parentLocationId,
                'pathString'              => '/1/5/' . $createdLocation->id . '/',
                'modifiedSubLocationDate' => null, // TODO: Should be DateTime
                'depth'                   => 2,
                'childCount'              => 0,
                'sortField'               => $locationCreate->sortField,
                'sortOrder'               => $locationCreate->sortOrder,
            ),
            $createdLocation
        );
        // 'isMainLocation' => $locationCreate->isMainLocation,
        $this->assertNotNull(
            $createdLocation->id
        );

        // TODO: Update $mainLocationId in ContentInfo, if set in
        // LocationCreateStruct
        // TODO: Check parent location childCount raised
        $this->markTestIncomplete( 'Outstanding TODOs.' );
    }

    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testCreateLocationParentChildCountRaised( array $data )
    {
        $parentLocation = $data['parentLocation'];

        $this->assertEquals( 6, $parentLocation->childCount );
    }

    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateLocationThrowsIllegalArgumentExceptionContentAlreadyBelowParent()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo( 108 );

        $locationCreate = $locationService->newLocationCreateStruct( 2 );

        // Throws exception, since content is already located at "/1/2/107/110/"
        $location = $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @todo This test case is not well-defined, yet. Re-check.
     */
    public function testCreateLocationThrowsIllegalArgumentExceptionParentIsSublocationOfContent()
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
    public function testCreateLocationThrowsIllegalArgumentExceptionRemoteIdExists()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo( 108 );

        $locationCreate = $locationService->newLocationCreateStruct( 5 );
        $locationCreate->remoteId = 'f3e90596361e31d496d4026eb624c983';

        // Throws exception, since remote ID is already in use
        $location = $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( 5 );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $location
        );
        return $location;
    }

    /**
     * Test for the loadLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationStructValues( Location $location )
    {
        $this->assertPropertiesCorrect(
            array(
                'id'                      =>  5,
                'priority'                =>  0,
                'hidden'                  =>  false,
                'invisible'               =>  false,
                'remoteId'                =>  '3f6d92f8044aed134f32153517850f5a',
                'parentLocationId'        =>  1,
                'pathString'              =>  '/1/5/',
                'modifiedSubLocationDate' =>  1311154216,
                'depth'                   =>  1,
                'sortField'               =>  1,
                'sortOrder'               =>  1,
                'childCount'              =>  5,
            ),
            $location
        );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\ContentInfo',
            $location->contentInfo
        );
        $this->assertEquals(
            4, $location->contentInfo->contentId
        );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        // Throws exception, if Location with ID 2342 does not exist
        $location = $locationService->loadLocation( 2342 );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @depth eZ\Publish\API\Repository\Tests\LocationServiceTest::loadLocation
     */
    public function testLoadLocationByRemoteId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocationByRemoteId(
            '3f6d92f8044aed134f32153517850f5a'
        );
        /* END: Use Case */

        $this->assertEquals(
            $locationService->loadLocation( 5 ),
            $location
        );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        // Throws exception, since Location with remote ID does not exist
        $location = $locationService->loadLocationByRemoteId(
            'not-exists'
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadMainLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadMainLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadMainLocation()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo( 4 );

        $location = $locationService->loadMainLocation(
            $contentInfo
        );
        /* END: Use Case */

        $this->assertEquals(
            $locationService->loadLocation( 5 ),
            $location
        );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentTypeService = $repository->getContentTypeService();
        $contentService     = $repository->getContentService();
        $locationService    = $repository->getLocationService();

        // Create new content, which is not published
        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $contentCreate = $contentService->newContentCreateStruct(
            $folderType, 'eng-US'
        );
        $contentCreate->setField( 'name', 'New Folder' );
        $content = $contentService->createContent( $contentCreate );

        // Throws Exception, since $content has no published version, yet
        $location = $locationService->loadMainLocation(
            $content->contentInfo
        );
        /* END: Use Case */
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo( 4 );

        $locations = $locationService->loadLocations( $contentInfo );
        /* BEGIN: Use Case */;

        $this->assertInternalType(
            'array', $locations
        );
        return $locations;
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     */
    public function testLoadLocationsContent( array $locations )
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $this->assertEquals( 1, count( $locations ) );
        foreach ( $locations as $loadedLocation )
        {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
                $loadedLocation
            );
        }

        usort(
            $locations,
            function ( $a, $b )
            {
                strcmp( $a->id, $b->id );
            }
        );

        $this->assertEquals(
            array(
                $locationService->loadLocation( 5 ),
            ),
            $locations
        );
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations($contentInfo, $rootLocation)
     * 
     */
    public function testLoadLocationsLimitedSubtree()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        // Location at "/1/48/54"
        $originalLocation = $locationService->loadLocation( 54 );

        // Create location under "/1/43/"
        $locationCreate = $locationService->newLocationCreateStruct( 43 );
        $locationService->createLocation(
            $originalLocation->contentInfo,
            $locationCreate
        );

        $findRootLocation = $locationService->loadLocation( 48 );

        // Returns an array with only $originalLocation
        $locations = $locationService->loadLocations(
            $originalLocation->contentInfo,
            $findRootLocation
        );
        /* BEGIN: Use Case */;

        $this->assertInternalType(
            'array', $locations
        );
        return $locations;
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationsLimitedSubtree
     */
    public function testLoadLocationsLimitedSubtreeContent( array $locations )
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $this->assertEquals( 1, count( $locations ) );

        $this->assertEquals(
            54,
            reset( $locations )->id
        );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentTypeService = $repository->getContentTypeService();
        $contentService     = $repository->getContentService();
        $locationService    = $repository->getLocationService();

        // Create new content, which is not published
        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $contentCreate = $contentService->newContentCreateStruct(
            $folderType, 'eng-US'
        );
        $contentCreate->setField( 'name', 'New Folder' );
        $content = $contentService->createContent( $contentCreate );

        // Throws Exception, since $content has no published version, yet
        $location = $locationService->loadLocations(
            $content->contentInfo
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations($contentInfo, $rootLocation)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testLoadLocationsThrowsBadStateExceptionLimitedSubtree()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentTypeService = $repository->getContentTypeService();
        $contentService     = $repository->getContentService();
        $locationService    = $repository->getLocationService();

        // Create new content, which is not published
        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $contentCreate = $contentService->newContentCreateStruct(
            $folderType, 'eng-US'
        );
        $contentCreate->setField( 'name', 'New Folder' );
        $content = $contentService->createContent( $contentCreate );

        $findRootLocation = $locationService->loadLocation( 1 );

        // Throws Exception, since $content has no published version, yet
        $location = $locationService->loadLocations(
            $content->contentInfo, $findRootLocation
        );
        /* END: Use Case */
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
