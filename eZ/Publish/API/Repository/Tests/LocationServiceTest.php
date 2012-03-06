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

use \eZ\Publish\API\Repository\Exceptions;

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
                // remoteId should be initialized with a default value
                //'remoteId'         => null,
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

        $this->assertNotNull( $createdLocation->id );
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionContentAlreadyBelowParent()
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @todo This test case is not well-defined, yet. Re-check.
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionParentIsSubLocationOfContent()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::createLocation() is not implemented." );
    }

    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionRemoteIdExists()
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
        $locationService->createLocation(
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
        /* END: Use Case */;

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
            array( 5 ),
            array_map(
                function ( Location $location )
                {
                    return $location->id;
                },
                $locations
            )
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
        /* END: Use Case */;

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
        $locationService->loadLocations(
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

        $findRootLocation = $locationService->loadLocation( 2 );

        // Throws Exception, since $content has no published version, yet
        $locationService->loadLocations(
            $content->contentInfo,
            $findRootLocation
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( 2 );

        $childLocations = $locationService->loadLocationChildren( $location );
        /* END: Use Case */;

        $this->assertInternalType(
            'array', $childLocations
        );
        return $childLocations;
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     */
    public function testLoadLocationChildrenData( array $locations )
    {
        $this->assertEquals( 9, count( $locations ) );

        foreach ( $locations as $location )
        {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
                $location
            );
        }

        $this->assertEquals(
            array( 69, 153, 96, 107, 77, 86, 156, 167, 190 ),
            array_map(
                function ( Location $location )
                {
                    return $location->id;
                },
                $locations
            )
        );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset)
     * 
     */
    public function testLoadLocationChildrenWithOffset()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( 2 );

        $childLocations = $locationService->loadLocationChildren(
            $location, 2
        );
        /* END: Use Case */;

        $this->assertInternalType(
            'array', $childLocations
        );
        return $childLocations;
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildrenWithOffset
     */
    public function testLoadLocationChildrenDataWithOffset( array $locations )
    {
        $this->assertEquals( 7, count( $locations ) );

        foreach ( $locations as $location )
        {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
                $location
            );
        }

        $this->assertEquals(
            array( 96, 107, 77, 86, 156, 167, 190 ),
            array_map(
                function ( Location $location )
                {
                    return $location->id;
                },
                $locations
            )
        );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset, $limit)
     * 
     */
    public function testLoadLocationChildrenWithOffsetAndLimit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( 2 );

        $childLocations = $locationService->loadLocationChildren(
            $location, 2, 3
        );
        /* END: Use Case */;

        $this->assertInternalType(
            'array', $childLocations
        );
        return $childLocations;
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset, $limit)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildrenWithOffsetAndLimit
     */
    public function testLoadLocationChildrenDataWithOffsetAndLimit( array $locations )
    {
        $this->assertEquals( 3, count( $locations ) );

        foreach ( $locations as $location )
        {
            $this->assertInstanceOf(
                '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
                $location
            );
        }

        $this->assertEquals(
            array( 96, 107, 77 ),
            array_map(
                function ( Location $location )
                {
                    return $location->id;
                },
                $locations
            )
        );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $updateStruct = $locationService->newLocationUpdateStruct();
        /* END: Use Case */;

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationUpdateStruct',
            $updateStruct
        );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation( 5 );

        $updateStruct = $locationService->newLocationUpdateStruct();
        $updateStruct->priority  = 3;
        $updateStruct->remoteId  = 'c7adcbf1e96bc29bca28c2d809d0c7ef69272651';
        $updateStruct->sortField = Location::SORT_FIELD_PRIORITY;
        $updateStruct->sortOrder = Location::SORT_ORDER_DESC;

        $updatedLocation = $locationService->updateLocation(
            $originalLocation, $updateStruct
        );
        /* END: Use Case */;

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $updatedLocation
        );

        return array(
            'originalLocation' => $originalLocation,
            'updateStruct'     => $updateStruct,
            'updatedLocation'  => $updatedLocation,
        );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testUpdateLocation
     */
    public function testUpdateLocationStructValues( array $data )
    {
        $originalLocation = $data['originalLocation'];
        $updateStruct     = $data['updateStruct'];
        $updatedLocation  = $data['updatedLocation'];

        $this->assertPropertiesCorrect(
            array(
                'id'                      => $originalLocation->id,
                'priority'                => $updateStruct->priority,
                'hidden'                  => $originalLocation->hidden,
                'invisible'               => $originalLocation->invisible,
                'remoteId'                => $updateStruct->remoteId,
                'contentInfo'             => $originalLocation->contentInfo,
                'parentLocationId'        => $originalLocation->parentLocationId,
                'pathString'              => $originalLocation->pathString,
                'modifiedSubLocationDate' => $originalLocation->modifiedSubLocationDate,
                'depth'                   => $originalLocation->depth,
                'sortField'               => $updateStruct->sortField,
                'sortOrder'               => $updateStruct->sortOrder,
                'childCount'              => $originalLocation->childCount,
            ),
            $updatedLocation
        );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateLocationThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation( 5 );

        $updateStruct = $locationService->newLocationUpdateStruct();
        // Remote ID of the root location
        $updateStruct->remoteId  = '629709ba256fe317c3ddcee35453a96a';

        // Throws exception, since remote ID is already taken
        $updatedLocation = $locationService->updateLocation(
            $originalLocation, $updateStruct
        );
        /* END: Use Case */;
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
        $this->markTestIncomplete( "@TODO: Test for LocationService::swapLocation() is not implemented." );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation( 5 );

        $hiddenLocation = $locationService->hideLocation( $visibleLocation );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $hiddenLocation
        );

        $this->assertTrue(
            $hiddenLocation->hidden,
            sprintf(
                'Location with ID "%s" not hidden.',
                $hiddenLocation->id
            )
        );
        foreach ( $locationService->loadLocationChildren( $hiddenLocation ) as $child )
        {
            $this->assertSubtreeProperties(
                array( 'invisible' => true ),
                $child
            );
        }
    }

    /**
     * Assert that $expectedValues are set in the subtree starting at $location
     *
     * @param array $expectedValues
     * @param Location $location
     * @return void
     */
    protected function assertSubtreeProperties( array $expectedValues, Location $location, $stopId = null )
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        if ( $location->id === $stopId )
        {
            return;
        }

        foreach ( $expectedValues as $propertyName => $propertyValue )
        {
            $this->assertEquals(
                $propertyValue,
                $location->$propertyName
            );

            foreach ( $locationService->loadLocationChildren( $location ) as $child )
            {
                $this->assertSubtreeProperties( $expectedValues, $child );
            }
        }
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation( 5 );
        $hiddenLocation = $locationService->hideLocation( $visibleLocation );

        $unHiddenLocation = $locationService->unhideLocation( $hiddenLocation );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $unHiddenLocation
        );

        $this->assertFalse(
            $unHiddenLocation->hidden,
            sprintf(
                'Location with ID "%s" not unhidden.',
                $unHiddenLocation->id
            )
        );
        foreach ( $locationService->loadLocationChildren( $unHiddenLocation ) as $child )
        {
            $this->assertSubtreeProperties(
                array( 'invisible' => false ),
                $child
            );
        }
    }

    /**
     * Test for the unhideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::unhideLocation()
     * 
     */
    public function testUnhideLocationNotUnhidesHiddenSubtree()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        $higherLocation = $locationService->loadLocation( 5 );
        $hiddenHigherLocation = $locationService->hideLocation( $higherLocation );

        $lowerLocation = $locationService->loadLocation( 13 );
        $hiddenLowerLocation = $locationService->hideLocation( $lowerLocation );

        $unHiddenHigherLocation = $locationService->unhideLocation( $hiddenHigherLocation );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location',
            $unHiddenHigherLocation
        );

        $this->assertFalse(
            $unHiddenHigherLocation->hidden,
            sprintf(
                'Location with ID "%s" not unhidden.',
                $unHiddenHigherLocation->id
            )
        );
        foreach ( $locationService->loadLocationChildren( $unHiddenHigherLocation ) as $child )
        {
            $this->assertSubtreeProperties(
                array( 'invisible' => false ),
                $child,
                13
            );
        }

        $stillHiddenLocation = $locationService->loadLocation( 13 );
        $this->assertTrue(
            $stillHiddenLocation->hidden,
            sprintf(
                'Hidden sub-location with ID %s accedentally unhidden.',
                $stillHiddenLocation->id
            )
        );
        foreach ( $locationService->loadLocationChildren( $stillHiddenLocation ) as $child )
        {
            $this->assertSubtreeProperties(
                array( 'invisible' => true ),
                $child
            );
        }
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( 13 );

        $locationService->deleteLocation( $location );
        /* END: Use Case */

        try
        {
            $locationService->loadLocation( 13 );
            $this->fail( "Location 13 not deleted." );
        }
        catch ( Exceptions\NotFoundException $e ) {}
        try
        {
            $locationService->loadLocation( 15 );
            $this->fail( "Location 15 not deleted." );
        }
        catch ( Exceptions\NotFoundException $e ) {}

        $contentService = $repository->getContentService();
        try
        {
            $contentService->loadContentInfo( 12 );
            $this->fail( "Content 12 at location 13 not delete." );
        }
        catch ( Exceptions\NotFoundException $e ) {}
        try
        {
            $contentService->loadContentInfo( 14 );
            $this->fail( "Content 14 at location 15 not delete." );
        }
        catch ( Exceptions\NotFoundException $e ) {}
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testDeleteLocation
     */
    public function testDeleteLocationDecrementsChildCountOnParent()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // ID of the "Administrator users" group in an eZ Publish demo installation
        $administratorsLocationId = 13;

        $locationService = $repository->getLocationService();

        // Load the current the user group location
        $location = $locationService->loadLocation( $administratorsLocationId );

        // Load the parent location
        $parentLocation = $locationService->loadLocation(
            $location->parentLocationId
        );

        // Get child count
        $childCountBefore = $parentLocation->childCount;

        // Delete the user group location
        $locationService->deleteLocation( $location );

        // Reload parent location
        $parentLocation = $locationService->loadLocation(
            $location->parentLocationId
        );

        // This will be $childCountBefore - 1
        $childCountAfter = $parentLocation->childCount;
        /* END: Use Case */

        $this->assertEquals( $childCountBefore - 1, $childCountAfter );
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
        $this->markTestIncomplete( "@TODO: Test for LocationService::copySubtree() is not implemented." );
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCopySubtreeThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "@TODO: Test for LocationService::copySubtree() is not implemented." );
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
        $this->markTestIncomplete( "@TODO: Test for LocationService::moveSubtree() is not implemented." );
    }
}
