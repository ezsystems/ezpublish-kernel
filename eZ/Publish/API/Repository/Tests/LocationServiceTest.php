<?php
/**
 * File containing the LocationServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationList;

use \eZ\Publish\API\Repository\Exceptions;

/**
 * Test case for operations in the LocationService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LocationService
 * @group location
 */
class LocationServiceTest extends BaseTest
{
    /**
     * Test for the newLocationCreateStruct() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct
     * @see \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetLocationService
     */
    public function testNewLocationCreateStruct()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId( 'location', 1 );
        /* BEGIN: Use Case */;
        // $parentLocationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $locationCreate = $locationService->newLocationCreateStruct(
            $parentLocationId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\LocationCreateStruct',
            $locationCreate
        );

        return $locationCreate;
    }

    /**
     * Test for the newLocationCreateStruct() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreate
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     */
    public function testNewLocationCreateStructValues( LocationCreateStruct $locationCreate )
    {
        $this->assertPropertiesCorrect(
            array(
                'priority' => 0,
                'hidden' => false,
                // remoteId should be initialized with a default value
                //'remoteId' => null,
                'sortField' => Location::SORT_FIELD_NAME,
                'sortOrder' => Location::SORT_ORDER_ASC,
                'parentLocationId' => $this->generateId( 'location', 1 ),
            ),
            $locationCreate
        );
    }

    /**
     * Test for the createLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     */
    public function testCreateLocation()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'object', 41 );
        $parentLocationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */;
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo( $contentId );

        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );
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
            '\eZ\Publish\API\Repository\Values\Content\Location',
            $location
        );

        return array(
            'locationCreate' => $locationCreate,
            'createdLocation' => $location,
            'contentInfo' => $contentInfo,
            'parentLocation' => $locationService->loadLocation( $this->generateId( 'location', 5 ) ),
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
        $locationCreate = $data['locationCreate'];
        $createdLocation = $data['createdLocation'];
        $contentInfo = $data['contentInfo'];

        $this->assertPropertiesCorrect(
            array(
                'priority' => $locationCreate->priority,
                'hidden' => $locationCreate->hidden,
                'invisible' => $locationCreate->hidden,
                'remoteId' => $locationCreate->remoteId,
                'contentInfo' => $contentInfo,
                'parentLocationId' => $locationCreate->parentLocationId,
                'pathString' => '/1/5/' . $this->parseId( 'location', $createdLocation->id ) . '/',
                'depth' => 2,
                'sortField' => $locationCreate->sortField,
                'sortOrder' => $locationCreate->sortOrder,
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionContentAlreadyBelowParent()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'object', 11 );
        $parentLocationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location which already
        // has the content assigned to one of its descendant locations
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo( $contentId );

        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        // Throws exception, since content is already located at "/1/2/107/110/"
        $locationService->createLocation(
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionParentIsSubLocationOfContent()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'object', 4 );
        $parentLocationId = $this->generateId( 'location', 12 );
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location which is below a
        // location that is assigned to the content
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo( $contentId );

        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );

        // Throws exception, since content is already located at "/1/2/"
        $locationService->createLocation(
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testNewLocationCreateStruct
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionRemoteIdExists()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'object', 41 );
        $parentLocationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // ContentInfo for "How to use eZ Publish"
        $contentInfo = $contentService->loadContentInfo( $contentId );

        $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );
        // This remote ID already exists
        $locationCreate->remoteId = 'f3e90596361e31d496d4026eb624c983';

        // Throws exception, since remote ID is already in use
        $locationService->createLocation(
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testCreateLocationInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'object', 41 );
        $parentLocationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $contentId is the ID of an existing content object
        // $parentLocationId is the ID of an existing location
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $repository->beginTransaction();

        try
        {
            // ContentInfo for "How to use eZ Publish"
            $contentInfo = $contentService->loadContentInfo( $contentId );

            $locationCreate = $locationService->newLocationCreateStruct( $parentLocationId );
            $locationCreate->remoteId = 'sindelfingen';

            $createdLocationId = $locationService->createLocation(
                $contentInfo,
                $locationCreate
            )->id;
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        $repository->rollback();

        try
        {
            // Throws exception since creation of location was rolled back
            $location = $locationService->loadLocation( $createdLocationId );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            return;
        }
        /* END: Use Case */

        $this->fail( 'Objects still exists after rollback.' );
    }

    /**
     * Test for the loadLocation() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testLoadLocation()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( $locationId );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Location',
            $location
        );
        return $location;
    }

    /**
     * Test for the loadLocation() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationStructValues( Location $location )
    {
        $this->assertPropertiesCorrect(
            array(
                'id' => $this->generateId( 'location', 5 ),
                'priority' => 0,
                'hidden' => false,
                'invisible' => false,
                'remoteId' => '3f6d92f8044aed134f32153517850f5a',
                'parentLocationId' => $this->generateId( 'location', 1 ),
                'pathString' => '/1/5/',
                'depth' => 1,
                'sortField' => 1,
                'sortOrder' => 1,
            ),
            $location
        );

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\ContentInfo',
            $location->contentInfo
        );
        $this->assertEquals(
            $this->generateId( 'object', 4 ), $location->contentInfo->id
        );
    }

    /**
     * Test for the loadLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadLocationThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentLocationId = $this->generateId( 'location', 2342 );
        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        // Throws exception, if Location with $nonExistentLocationId does not
        // exist
        $location = $locationService->loadLocation( $nonExistentLocationId );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
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
            $locationService->loadLocation( $this->generateId( 'location', 5 ) ),
            $location
        );
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
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
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testLoadLocations()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'object', 4 );
        /* BEGIN: Use Case */;
        // $contentId contains the ID of an existing content object
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo( $contentId );

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
                '\eZ\Publish\API\Repository\Values\Content\Location',
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
            array( $this->generateId( 'location', 5 ) ),
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
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations($contentInfo, $rootLocation)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     */
    public function testLoadLocationsLimitedSubtree()
    {
        $repository = $this->getRepository();

        $originalLocationId = $this->generateId( 'location', 54 );
        $originalParentLocationId = $this->generateId( 'location', 48 );
        $newParentLocationId = $this->generateId( 'location', 43 );
        /* BEGIN: Use Case */;
        // $originalLocationId is the ID of an existing location
        // $originalParentLocationId is the ID of the parent location of
        //     $originalLocationId
        // $newParentLocationId is the ID of an existing location outside the tree
        // of $originalLocationId and $originalParentLocationId
        $locationService = $repository->getLocationService();

        // Location at "/1/48/54"
        $originalLocation = $locationService->loadLocation( $originalLocationId );

        // Create location under "/1/43/"
        $locationCreate = $locationService->newLocationCreateStruct( $newParentLocationId );
        $locationService->createLocation(
            $originalLocation->contentInfo,
            $locationCreate
        );

        $findRootLocation = $locationService->loadLocation( $originalParentLocationId );

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
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $locations
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationsLimitedSubtree
     */
    public function testLoadLocationsLimitedSubtreeContent( array $locations )
    {
        $this->assertEquals( 1, count( $locations ) );

        $this->assertEquals(
            $this->generateId( 'location', 54 ),
            reset( $locations )->id
        );
    }

    /**
     * Test for the loadLocations() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testLoadLocationsThrowsBadStateException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testLoadLocationsThrowsBadStateExceptionLimitedSubtree()
    {
        $repository = $this->getRepository();

        $someLocationId = $this->generateId( 'location', 2 );
        /* BEGIN: Use Case */
        // $someLocationId is the ID of an existing location
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        // Create new content, which is not published
        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $contentCreate = $contentService->newContentCreateStruct(
            $folderType, 'eng-US'
        );
        $contentCreate->setField( 'name', 'New Folder' );
        $content = $contentService->createContent( $contentCreate );

        $findRootLocation = $locationService->loadLocation( $someLocationId );

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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationChildren()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( $locationId );

        $childLocations = $locationService->loadLocationChildren( $location );
        /* END: Use Case */;

        $this->assertInstanceOf(
            "\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationList", $childLocations
        );
        $this->assertInternalType(
            'array', $childLocations->locations
        );
        $this->assertInternalType(
            'int', $childLocations->totalCount
        );
        return $childLocations;
    }

    /**
     * Test for the getLocationChildCount() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::getLocationChildCount()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testGetLocationChildCount()
    {
        // $locationId is the ID of an existing location
        $locationService = $this->getRepository()->getLocationService();

        $this->assertSame(
            5,
            $locationService->getLocationChildCount(
                $locationService->loadLocation( $this->generateId( 'location', 5 ) )
            )
        );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     */
    public function testLoadLocationChildrenData( LocationList $locations )
    {
        $this->assertEquals( 5, count( $locations->locations ) );
        $this->assertEquals( 5, $locations->totalCount );

        foreach ( $locations->locations as $location )
        {
            $this->assertInstanceOf(
                '\eZ\Publish\API\Repository\Values\Content\Location',
                $location
            );
        }

        $this->assertEquals(
            array(
                $this->generateId( 'location', 12 ),
                $this->generateId( 'location', 13 ),
                $this->generateId( 'location', 14 ),
                $this->generateId( 'location', 44 ),
                $this->generateId( 'location', 61 ),
            ),
            array_map(
                function ( Location $location )
                {
                    return $location->id;
                },
                $locations->locations
            )
        );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     */
    public function testLoadLocationChildrenWithOffset()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( $locationId );

        $childLocations = $locationService->loadLocationChildren(
            $location, 2
        );
        /* END: Use Case */;

        $this->assertInstanceOf(
            "\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationList", $childLocations
        );
        $this->assertInternalType(
            'array', $childLocations->locations
        );
        $this->assertInternalType(
            'int', $childLocations->totalCount
        );
        return $childLocations;
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationList $locations
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildrenWithOffset
     */
    public function testLoadLocationChildrenDataWithOffset( LocationList $locations )
    {
        $this->assertEquals( 3, count( $locations->locations ) );
        $this->assertEquals( 5, $locations->totalCount );

        foreach ( $locations->locations as $location )
        {
            $this->assertInstanceOf(
                '\eZ\Publish\API\Repository\Values\Content\Location',
                $location
            );
        }

        $this->assertEquals(
            array(
                $this->generateId( 'location', 14 ),
                $this->generateId( 'location', 44 ),
                $this->generateId( 'location', 61 ),
            ),
            array_map(
                function ( Location $location )
                {
                    return $location->id;
                },
                $locations->locations
            )
        );
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location[]
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset, $limit)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildren
     */
    public function testLoadLocationChildrenWithOffsetAndLimit()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( $locationId );

        $childLocations = $locationService->loadLocationChildren(
            $location, 2, 2
        );
        /* END: Use Case */;

        $this->assertInstanceOf(
            "\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationList", $childLocations
        );
        $this->assertInternalType(
            'array', $childLocations->locations
        );
        $this->assertInternalType(
            'int', $childLocations->totalCount
        );
        return $childLocations;
    }

    /**
     * Test for the loadLocationChildren() method.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location[] $locations
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationChildren($location, $offset, $limit)
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationChildrenWithOffsetAndLimit
     */
    public function testLoadLocationChildrenDataWithOffsetAndLimit( LocationList $locations )
    {
        $this->assertEquals( 2, count( $locations->locations ) );
        $this->assertEquals( 5, $locations->totalCount );

        foreach ( $locations->locations as $location )
        {
            $this->assertInstanceOf(
                '\eZ\Publish\API\Repository\Values\Content\Location',
                $location
            );
        }

        $this->assertEquals(
            array(
                $this->generateId( 'location', 14 ),
                $this->generateId( 'location', 44 ),
            ),
            array_map(
                function ( Location $location )
                {
                    return $location->id;
                },
                $locations->locations
            )
        );
    }

    /**
     * Test for the newLocationUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::newLocationUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetLocationService
     */
    public function testNewLocationUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */;
        $locationService = $repository->getLocationService();

        $updateStruct = $locationService->newLocationUpdateStruct();
        /* END: Use Case */;

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct',
            $updateStruct
        );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testUpdateLocation()
    {
        $repository = $this->getRepository();

        $originalLocationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */;
        // $originalLocationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation( $originalLocationId );

        $updateStruct = $locationService->newLocationUpdateStruct();
        $updateStruct->priority = 3;
        $updateStruct->remoteId = 'c7adcbf1e96bc29bca28c2d809d0c7ef69272651';
        $updateStruct->sortField = Location::SORT_FIELD_PRIORITY;
        $updateStruct->sortOrder = Location::SORT_ORDER_DESC;

        $updatedLocation = $locationService->updateLocation(
            $originalLocation, $updateStruct
        );
        /* END: Use Case */;

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Location',
            $updatedLocation
        );

        return array(
            'originalLocation' => $originalLocation,
            'updateStruct' => $updateStruct,
            'updatedLocation' => $updatedLocation,
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
        $updateStruct = $data['updateStruct'];
        $updatedLocation = $data['updatedLocation'];

        $this->assertPropertiesCorrect(
            array(
                'id' => $originalLocation->id,
                'priority' => $updateStruct->priority,
                'hidden' => $originalLocation->hidden,
                'invisible' => $originalLocation->invisible,
                'remoteId' => $updateStruct->remoteId,
                'contentInfo' => $originalLocation->contentInfo,
                'parentLocationId' => $originalLocation->parentLocationId,
                'pathString' => $originalLocation->pathString,
                'depth' => $originalLocation->depth,
                'sortField' => $updateStruct->sortField,
                'sortOrder' => $updateStruct->sortOrder,
            ),
            $updatedLocation
        );
    }

    /**
     * Test for the updateLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateLocationThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation( $locationId );

        $updateStruct = $locationService->newLocationUpdateStruct();
        // Remote ID of an existing location
        $updateStruct->remoteId = 'f3e90596361e31d496d4026eb624c983';

        // Throws exception, since remote ID is already taken
        $locationService->updateLocation(
            $originalLocation, $updateStruct
        );
        /* END: Use Case */;
    }

    /**
     * Test for the swapLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::swapLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testSwapLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );

        $mediaContentInfo = $locationService->loadLocation( $mediaLocationId )->getContentInfo();
        $demoDesignContentInfo = $locationService->loadLocation( $demoDesignLocationId )->getContentInfo();

        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        $mediaLocation = $locationService->loadLocation( $mediaLocationId );
        $demoDesignLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Swaps the content referred to by the locations
        $locationService->swapLocation( $mediaLocation, $demoDesignLocation );
        /* END: Use Case */

        $this->assertEquals(
            $mediaContentInfo->id,
            $locationService->loadLocation( $demoDesignLocationId )->getContentInfo()->id
        );
        $this->assertEquals(
            $demoDesignContentInfo->id,
            $locationService->loadLocation( $mediaLocationId )->getContentInfo()->id
        );
    }

    /**
     * Test for the hideLocation() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::hideLocation()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testHideLocation()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation( $locationId );

        $hiddenLocation = $locationService->hideLocation( $visibleLocation );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Location',
            $hiddenLocation
        );

        $this->assertTrue(
            $hiddenLocation->hidden,
            sprintf(
                'Location with ID "%s" not hidden.',
                $hiddenLocation->id
            )
        );
        foreach ( $locationService->loadLocationChildren( $hiddenLocation )->locations as $child )
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

            foreach ( $locationService->loadLocationChildren( $location )->locations as $child )
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testHideLocation
     */
    public function testUnhideLocation()
    {
        $repository = $this->getRepository();

        $locationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $locationId is the ID of an existing location
        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation( $locationId );
        $hiddenLocation = $locationService->hideLocation( $visibleLocation );

        $unHiddenLocation = $locationService->unhideLocation( $hiddenLocation );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Location',
            $unHiddenLocation
        );

        $this->assertFalse(
            $unHiddenLocation->hidden,
            sprintf(
                'Location with ID "%s" not unhidden.',
                $unHiddenLocation->id
            )
        );
        foreach ( $locationService->loadLocationChildren( $unHiddenLocation )->locations as $child )
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testUnhideLocation
     */
    public function testUnhideLocationNotUnhidesHiddenSubtree()
    {
        $repository = $this->getRepository();

        $higherLocationId = $this->generateId( 'location', 5 );
        $lowerLocationId = $this->generateId( 'location', 13 );
        /* BEGIN: Use Case */
        // $higherLocationId is the ID of a location
        // $lowerLocationId is the ID of a location below $higherLocationId
        $locationService = $repository->getLocationService();

        $higherLocation = $locationService->loadLocation( $higherLocationId );
        $hiddenHigherLocation = $locationService->hideLocation( $higherLocation );

        $lowerLocation = $locationService->loadLocation( $lowerLocationId );
        $hiddenLowerLocation = $locationService->hideLocation( $lowerLocation );

        $unHiddenHigherLocation = $locationService->unhideLocation( $hiddenHigherLocation );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Location',
            $unHiddenHigherLocation
        );

        $this->assertFalse(
            $unHiddenHigherLocation->hidden,
            sprintf(
                'Location with ID "%s" not unhidden.',
                $unHiddenHigherLocation->id
            )
        );
        foreach ( $locationService->loadLocationChildren( $unHiddenHigherLocation )->locations as $child )
        {
            $this->assertSubtreeProperties(
                array( 'invisible' => false ),
                $child,
                $this->generateId( 'location', 13 )
            );
        }

        $stillHiddenLocation = $locationService->loadLocation( $this->generateId( 'location', 13 ) );
        $this->assertTrue(
            $stillHiddenLocation->hidden,
            sprintf(
                'Hidden sub-location with ID %s accedentally unhidden.',
                $stillHiddenLocation->id
            )
        );
        foreach ( $locationService->loadLocationChildren( $stillHiddenLocation )->locations as $child )
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
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testDeleteLocation()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId( 'location', 43 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the location of the
        // "Media" location in an eZ Publish demo installation
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation( $mediaLocationId );

        $locationService->deleteLocation( $location );
        /* END: Use Case */

        try
        {
            $locationService->loadLocation( $mediaLocationId );
            $this->fail( "Location $mediaLocationId not deleted." );
        }
        catch ( Exceptions\NotFoundException $e ) {}

        // The following IDs are IDs of child locations of $mediaLocationId location
        // ( Media/Images, Media/Files, Media/Multimedia respectively )
        foreach ( array( 51, 52, 53 ) as $childLocationId )
        {
            try
            {
                $locationService->loadLocation( $this->generateId( 'location', $childLocationId ) );
                $this->fail( "Location $childLocationId not deleted." );
            }
            catch ( Exceptions\NotFoundException $e ) {}
        }

        // The following IDs are IDs of content below $mediaLocationId location
        // ( Media/Images, Media/Files, Media/Multimedia respectively )
        $contentService = $this->getRepository()->getContentService();
        foreach ( array( 49, 50, 51 ) as $childContentId )
        {
            try
            {
                $contentService->loadContentInfo( $this->generateId( 'object', $childContentId ) );
                $this->fail( "Content $childContentId not deleted." );
            }
            catch ( Exceptions\NotFoundException $e ) {}
        }
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

        $mediaLocationId = $this->generateId( 'location', 43 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the location of the
        // "Media" location in an eZ Publish demo installation

        $locationService = $repository->getLocationService();

        // Load the current the user group location
        $location = $locationService->loadLocation( $mediaLocationId );

        // Load the parent location
        $parentLocation = $locationService->loadLocation(
            $location->parentLocationId
        );

        // Get child count
        $childCountBefore = $locationService->getLocationChildCount( $parentLocation );

        // Delete the user group location
        $locationService->deleteLocation( $location );

        // Reload parent location
        $parentLocation = $locationService->loadLocation(
            $location->parentLocationId
        );

        // This will be $childCountBefore - 1
        $childCountAfter = $locationService->getLocationChildCount( $parentLocation );
        /* END: Use Case */

        $this->assertEquals( $childCountBefore - 1, $childCountAfter );
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testCopySubtree()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation( $mediaLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Copy location "Media" to "Demo Design"
        $copiedLocation = $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Location',
            $copiedLocation
        );

        $this->assertPropertiesCorrect(
            array(
                'depth' => $newParentLocation->depth + 1,
                'parentLocationId' => $newParentLocation->id,
                'pathString' => "{$newParentLocation->pathString}" . $this->parseId( 'location', $copiedLocation->id ) . "/"
            ),
            $copiedLocation
        );
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCopySubtree
     */
    public function testCopySubtreeUpdatesSubtreeProperties()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $locationToCopy = $locationService->loadLocation( $this->generateId( 'location', 43 ) );

        // Load Subtree properties before copy
        $expected = $this->loadSubtreeProperties( $locationToCopy );

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation( $mediaLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Copy location "Media" to "Demo Design"
        $copiedLocation = $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */

        $beforeIds = array();
        foreach ( $expected as $properties )
        {
            $beforeIds[] = $properties['id'];
        }

        // Load Subtree properties after copy
        $actual = $this->loadSubtreeProperties( $copiedLocation );

        $this->assertEquals( count( $expected ), count( $actual ) );

        foreach ( $actual as $properties )
        {
            $this->assertNotContains( $properties['id'], $beforeIds );
            $this->assertStringStartsWith(
                "{$newParentLocation->pathString}" . $this->parseId( 'location', $copiedLocation->id ) . "/",
                $properties['pathString']
            );
            $this->assertStringEndsWith(
                "/" . $this->parseId( 'location' , $properties['id'] ) . "/",
                $properties['pathString']
            );
        }
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCopySubtree
     */
    public function testCopySubtreeIncrementsChildCountOfNewParent()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $childCountBefore = $locationService->getLocationChildCount( $locationService->loadLocation( 56 ) );

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation( $mediaLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Copy location "Media" to "Demo Design"
        $copiedLocation = $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */

        $childCountAfter = $locationService->getLocationChildCount( $locationService->loadLocation( $demoDesignLocationId ) );

        $this->assertEquals( $childCountBefore + 1, $childCountAfter );
    }

    /**
     * Test for the copySubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCopySubtree
     */
    public function testCopySubtreeThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        $communityLocationId = $this->generateId( 'location', 5 );
        /* BEGIN: Use Case */
        // $communityLocationId is the ID of the "Community" page location in
        // an eZ Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation( $communityLocationId );

        // Use a child as new parent
        $childLocations = $locationService->loadLocationChildren( $locationToCopy )->locations;
        $newParentLocation = end( $childLocations );

        // This call will fail with an "InvalidArgumentException", because the
        // new parent is a child location of the subtree to copy.
        $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testMoveSubtree()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation( $mediaLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Move location from "Home" to "Demo Design"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation( $mediaLocationId );
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            array(
                'depth' => $newParentLocation->depth + 1,
                'parentLocationId' => $newParentLocation->id,
                'pathString' => "{$newParentLocation->pathString}" . $this->parseId( 'location' , $movedLocation->id ) . "/"
            ),
            $movedLocation
        );
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeUpdatesSubtreeProperties()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $locationToMove = $locationService->loadLocation( $this->generateId( 'location', 43 ) );
        $newParentLocation = $locationService->loadLocation( $this->generateId( 'location', 56 ) );

        // Load Subtree properties before move
        $expected = $this->loadSubtreeProperties( $locationToMove );
        foreach ( $expected as $id => $properties )
        {
            $expected[$id]['depth'] = $properties['depth'] + 2;
            $expected[$id]['pathString'] = str_replace(
                $locationToMove->pathString,
                "{$newParentLocation->pathString}" . $this->parseId( 'location', $locationToMove->id ) . "/",
                $properties['pathString']
            );
        }

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation( $mediaLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Move location from "Home" to "Demo Design"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation( $mediaLocationId );
        /* END: Use Case */

        // Load Subtree properties after move
        $actual = $this->loadSubtreeProperties( $movedLocation );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeIncrementsChildCountOfNewParent()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $newParentLocation = $locationService->loadLocation( $this->generateId( 'location', 56 ) );

        // Load expected properties before move
        $expected = $this->loadLocationProperties( $newParentLocation );
        $childCountBefore = $locationService->getLocationChildCount( $newParentLocation );

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation( $mediaLocationId );

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Move location from "Home" to "Demo Design"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Load moved location
        $movedLocation = $locationService->loadLocation( $mediaLocationId );

        // Reload new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );
        /* END: Use Case */

        // Load Subtree properties after move
        $actual = $this->loadLocationProperties( $newParentLocation );
        $childCountAfter = $locationService->getLocationChildCount( $newParentLocation );

        $this->assertEquals( $expected, $actual );
        $this->assertEquals( $childCountBefore + 1, $childCountAfter );
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeDecrementsChildCountOfOldParent()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $oldParentLocation = $locationService->loadLocation( $this->generateId( 'location', 1 ) );

        // Load expected properties before move
        $expected = $this->loadLocationProperties( $oldParentLocation );
        $childCountBefore = $locationService->getLocationChildCount( $oldParentLocation );

        $mediaLocationId = $this->generateId( 'location', 43 );
        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation( $mediaLocationId );

        // Get the location id of the old parent
        $oldParentLocationId = $locationToMove->parentLocationId;

        // Load new parent location
        $newParentLocation = $locationService->loadLocation( $demoDesignLocationId );

        // Move location from "Home" to "Demo Design"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );

        // Reload old parent location
        $oldParentLocation = $locationService->loadLocation( $oldParentLocationId );
        /* END: Use Case */

        // Load Subtree properties after move
        $actual = $this->loadLocationProperties( $oldParentLocation );
        $childCountAfter = $locationService->getLocationChildCount( $oldParentLocation );

        $this->assertEquals( $expected, $actual );
        $this->assertEquals( $childCountBefore - 1, $childCountAfter );
    }

    /**
     * Loads properties from all locations in the $location's subtree
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param array $properties
     * @return array
     */
    private function loadSubtreeProperties( Location $location, array $properties = array() )
    {
        $locationService = $this->getRepository()->getLocationService();

        foreach ( $locationService->loadLocationChildren( $location )->locations as $childLocation )
        {
            $properties[] = $this->loadLocationProperties( $childLocation );

            $properties = $this->loadSubtreeProperties( $childLocation, $properties );
        }

        return $properties;
    }

    /**
     * Loads assertable properties from the given location.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param mixed[] $overwrite
     * @return array
     */
    private function loadLocationProperties( Location $location, array $overwrite = array() )
    {
        return array_merge(
            array(
                'id' => $location->id,
                'depth' => $location->depth,
                'parentLocationId' => $location->parentLocationId,
                'pathString' => $location->pathString,
                'remoteId' => $location->remoteId,
                'hidden' => $location->hidden,
                'invisible' => $location->invisible,
                'priority' => $location->priority,
                'sortField' => $location->sortField,
                'sortOrder' => $location->sortOrder
            ),
            $overwrite
        );
    }

}
