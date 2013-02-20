<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\LocationBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;

/**
 * Test case for Location Service
 */
abstract class LocationBase extends BaseServiceTest
{
    /**
     * Test a new class and default values on properties
     * @covers \eZ\Publish\API\Repository\Values\Content\Location::__construct
     */
    public function testNewClass()
    {
        $location = new Location();

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'priority' => null,
                'hidden' => null,
                'invisible' => null,
                'remoteId' => null,
                'parentLocationId' => null,
                'pathString' => null,
                'path' => array(),
                'depth' => null,
                'sortField' => null,
                'sortOrder' => null,
            ),
            $location
        );
    }

    /**
     * Test retrieving missing property
     * @covers \eZ\Publish\API\Repository\Values\Content\Location::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $location = new Location();
            $value = $location->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch ( PropertyNotFound $e )
        {
        }
    }

    /**
     * Test setting read only property
     * @covers \eZ\Publish\API\Repository\Values\Content\Location::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $location = new Location();
            $location->id = 42;
            self::fail( "Succeeded setting read only property" );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test if property exists
     * @covers \eZ\Publish\API\Repository\Values\Content\Location::__isset
     */
    public function testIsPropertySet()
    {
        $location = new Location();
        $value = isset( $location->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $location->id );
        self::assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\Content\Location::__unset
     */
    public function testUnsetProperty()
    {
        $location = new Location( array( "id" => 2 ) );
        try
        {
            unset( $location->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * @param int $parentLocationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createTestContentLocation( $parentLocationId )
    {
        $contentService = $this->repository->getContentService();
        $contentTypeService = $this->repository->getContentTypeService();
        // User Group content type
        $contentType = $contentTypeService->loadContentType( 3 );

        $contentCreate = $contentService->newContentCreateStruct( $contentType, 'eng-GB' );
        $contentCreate->setField( "name", "dummy value" );
        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = md5( uniqid( get_class( $this ), true ) );
        $contentCreate->alwaysAvailable = true;

        $locationCreates = array(
            new LocationCreateStruct(
                array(
                    //priority = 0
                    //hidden = false
                    "remoteId" => md5( uniqid( get_class( $this ), true ) ),
                    //sortField = Location::SORT_FIELD_NAME
                    //sortOrder = Location::SORT_ORDER_ASC
                    "parentLocationId" => $parentLocationId
                )
            )
        );

        return $contentService->publishVersion(
            $contentService->createContent(
                $contentCreate,
                $locationCreates
            )->versionInfo
        );
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param int $parentLocationId
     *
     * @return mixed
     */
    protected function addNewMainLocation( $contentInfo, $parentLocationId )
    {
        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();

        $newLocation = $locationService->createLocation(
            $contentInfo,
            new LocationCreateStruct(
                array(
                    "remoteId" => md5( uniqid( get_class( $this ), true ) ),
                    "parentLocationId" => $parentLocationId
                )
            )
        );
        $contentService->updateContentMetadata(
            $contentInfo,
            new ContentMetadataUpdateStruct( array( "mainLocationId" => $newLocation->id ) )
        );

        return $newLocation->id;
    }

    /**
     * Test copying a subtree
     *
     * @group current
     * @covers \eZ\Publish\API\Repository\LocationService::copySubtree
     */
    public function testCopySubtree()
    {
        // Prepare test tree
        $outsideLocationId = $this->createTestContentLocation( 5 )->contentInfo->mainLocationId;
        $targetLocationId = $this->createTestContentLocation( 5 )->contentInfo->mainLocationId;
        $locationToCopyContent = $this->createTestContentLocation( 5 );

        $locationToCopyId = $locationToCopyContent->contentInfo->mainLocationId;

        $this->createTestContentLocation( $locationToCopyId );
        $subLocationContent20 = $this->createTestContentLocation( $locationToCopyId );
        $subLocationId20 = $subLocationContent20->contentInfo->mainLocationId;
        $subLocationContent21 = $this->createTestContentLocation( $subLocationId20 );

        // Add main locations outside subtree
        $this->addNewMainLocation( $locationToCopyContent->contentInfo, $outsideLocationId );
        $this->addNewMainLocation( $subLocationContent20->contentInfo, $outsideLocationId );

        // Add main locations inside subtree
        $lastLocationId = $this->addNewMainLocation( $subLocationContent21->contentInfo, $locationToCopyId );

        /* BEGIN: Use Case */
        $locationService = $this->repository->getLocationService();
        $locationToCopy = $locationService->loadLocation( $locationToCopyId );
        $targetLocation = $locationService->loadLocation( $targetLocationId );

        $copiedSubtreeRootLocation = $locationService->copySubtree( $locationToCopy, $targetLocation );
        /* END: Use Case */

        self::assertEquals( $lastLocationId + 1, $copiedSubtreeRootLocation->id );

        // Check structure
        $subtreeRootChildren = $locationService->loadLocationChildren( $copiedSubtreeRootLocation );
        self::assertEquals( 3, $subtreeRootChildren->totalCount );
        self::assertEquals( 0, $locationService->getLocationChildCount( $subtreeRootChildren->locations[0] ) );
        $subLocationChildren = $locationService->loadLocationChildren( $subtreeRootChildren->locations[1] );
        self::assertEquals( 1, $subLocationChildren->totalCount );
        self::assertEquals( 0, $locationService->getLocationChildCount( $subtreeRootChildren->locations[2] ) );
        self::assertEquals( 0, $locationService->getLocationChildCount( $subLocationChildren->locations[0] ) );

        // Check main locations
        self::assertEquals( $copiedSubtreeRootLocation->contentInfo->mainLocationId, $copiedSubtreeRootLocation->id );
        self::assertEquals( $subtreeRootChildren->locations[0]->contentInfo->mainLocationId, $subtreeRootChildren->locations[0]->id );
        self::assertEquals( $subtreeRootChildren->locations[1]->contentInfo->mainLocationId, $subtreeRootChildren->locations[1]->id );
        self::assertEquals( $subtreeRootChildren->locations[2]->contentInfo->mainLocationId, $subtreeRootChildren->locations[2]->id );
        self::assertEquals( $subLocationChildren->locations[0]->contentInfo->mainLocationId, $subtreeRootChildren->locations[2]->id );

        self::assertInstanceOf( "eZ\\Publish\\API\\Repository\\Values\\Content\\Location", $copiedSubtreeRootLocation );
        self::assertEquals( $targetLocation->id, $copiedSubtreeRootLocation->parentLocationId );
    }

    /**
     * Test copying a subtree throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LocationService::copySubtree
     */
    public function testCopySubtreeThrowsInvalidArgumentException()
    {
        $locationService = $this->repository->getLocationService();
        $locationToCopy = $locationService->loadLocation( 5 );
        $targetLocation = $locationService->loadLocation( 44 );

        $locationService->copySubtree( $locationToCopy, $targetLocation );
    }

    /**
     * Test loading a location
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocation
     */
    public function testLoadLocation()
    {
        $locationService = $this->repository->getLocationService();
        $loadedLocation = $locationService->loadLocation( 5 );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $loadedLocation );
        self::assertEquals( 5, $loadedLocation->id );
    }

    /**
     * Test loading a location throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocation
     */
    public function testLoadLocationThrowsNotFoundException()
    {
        $locationService = $this->repository->getLocationService();
        $locationService->loadLocation( PHP_INT_MAX );
    }

    /**
     * Test loading location by remote ID
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId
     */
    public function testLoadLocationByRemoteId()
    {
        $location = $this->repository->getLocationService()->loadLocationByRemoteId( '769380b7aa94541679167eab817ca893' );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $location );
        self::assertGreaterThan( 0, $location->id );
        self::assertEquals( '769380b7aa94541679167eab817ca893', $location->remoteId );
    }

   /**
     * Test loading location by remote ID
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId
     */
    public function testLoadLocationByRemoteIdThrowsNotFoundException()
    {
        $this->repository->getLocationService()->loadLocationByRemoteId( "not-existing" );
    }

    protected function createContentDraft()
    {
        $contentTypeService = $this->repository->getContentTypeService();
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $contentCreateStruct = $contentService->newContentCreateStruct(
            $contentTypeService->loadContentType( 3 ),
            'eng-GB'
        );
        $contentCreateStruct->sectionId = 1;
        $contentCreateStruct->ownerId = 14;

        $contentCreateStruct->setField( 'name', 'New group' );
        $contentCreateStruct->setField( 'description', 'New group description' );

        $locationCreateStruct = $locationService->newLocationCreateStruct( 5 );

        return $contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
    }

    /**
     * Test loading locations for content
     *
     * @covers \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocations
     */
    public function testLoadLocations()
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo( 12 );

        $locationService = $this->repository->getLocationService();
        $locations = $locationService->loadLocations( $contentInfo );

        self::assertInternalType( "array", $locations );
        self::assertNotEmpty( $locations );

        foreach ( $locations as $location )
        {
            self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $location );
            self::assertEquals( $contentInfo->id, $location->getContentInfo()->id );
        }

        $locationsCount = count( $locations );

        $locationCreateStruct = $locationService->newLocationCreateStruct( 44 );
        $locationService->createLocation( $contentInfo, $locationCreateStruct );

        $locations = $locationService->loadLocations( $contentInfo );

        self::assertInternalType( "array", $locations );
        self::assertNotEmpty( $locations );

        foreach ( $locations as $location )
        {
            self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $location );
            self::assertEquals( $contentInfo->id, $location->getContentInfo()->id );
        }

        $newLocationsCount = count( $locations );

        self::assertEquals( $locationsCount + 1, $newLocationsCount );
    }

    /**
     * Test loading locations for content with root location specified
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocations
     */
    public function testLoadLocationsWithRootLocation()
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo( 12 );

        $locationService = $this->repository->getLocationService();
        $parentLocation = $locationService->loadLocation( 5 );

        $locations = $locationService->loadLocations( $contentInfo, $parentLocation );

        self::assertInternalType( "array", $locations );
        self::assertNotEmpty( $locations );

        foreach ( $locations as $location )
        {
            self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $location );
        }

        foreach ( $locations as $location )
        {
            if ( stripos( $location->pathString, $parentLocation->pathString ) === false )
                self::fail( "fetched locations outside root node" );
        }
    }

    /**
     * Test loading locations for content throwing BadStateException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocations
     */
    public function testLoadLocationsThrowsBadStateException()
    {
        $contentDraft = $this->createContentDraft();

        $this->repository->getLocationService()->loadLocations(
            $contentDraft->getVersionInfo()->getContentInfo()
        );
    }

    /**
     * Test loading location children
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationChildren
     */
    public function testLoadLocationChildren()
    {
        $locationService = $this->repository->getLocationService();

        $rootLocation = $locationService->loadLocation( 5 );
        $childrenLocations = $locationService->loadLocationChildren( $rootLocation );

        self::assertInstanceOf( "\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationList", $childrenLocations );
        self::assertInternalType( "array", $childrenLocations->locations );
        self::assertInternalType( "int", $childrenLocations->totalCount );
        self::assertNotEmpty( $childrenLocations->locations );

        foreach ( $childrenLocations->locations as $childLocation )
        {
            self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $childLocation );
            self::assertEquals( $rootLocation->id, $childLocation->parentLocationId );
        }
    }

    /**
     * Test creating a location
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     */
    public function testCreateLocation()
    {
        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();

        $parentLocation = $locationService->loadLocation( 44 );

        $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocation->id );
        $locationCreateStruct->priority = 42;
        $locationCreateStruct->remoteId = 'new-remote-id';
        $locationCreateStruct->hidden = true;
        $locationCreateStruct->sortField = Location::SORT_FIELD_DEPTH;
        $locationCreateStruct->sortOrder = Location::SORT_ORDER_DESC;

        $contentInfo = $contentService->loadContentInfo( 12 );

        $createdLocation = $locationService->createLocation(
            $contentInfo,
            $locationCreateStruct
        );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $createdLocation );
        self::assertGreaterThan( 0, $createdLocation->id );
        $createdPath = $parentLocation->path;
        $createdPath[] = $createdLocation->id;

        $this->assertPropertiesCorrect(
            array(
                'priority' => $locationCreateStruct->priority,
                'hidden' => $locationCreateStruct->hidden,
                'invisible' => $locationCreateStruct->hidden,
                'remoteId' => $locationCreateStruct->remoteId,
                'parentLocationId' => $locationCreateStruct->parentLocationId,
                'pathString' => $parentLocation->pathString . $createdLocation->id . '/',
                'path' => $createdPath,
                'depth' => $parentLocation->depth + 1,
                'sortField' => $locationCreateStruct->sortField,
                'sortOrder' => $locationCreateStruct->sortOrder,
            ),
            $createdLocation
        );

        self::assertEquals( $contentInfo->id, $createdLocation->getContentInfo()->id );
    }

    /**
     * Test creating a location throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionLocationExistsBelowParent()
    {
        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();

        $locationCreateStruct = $locationService->newLocationCreateStruct( 5 );
        $contentInfo = $contentService->loadContentInfo( 12 );
        $locationService->createLocation( $contentInfo, $locationCreateStruct );
    }

    /**
     * Test creating a location throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionExistingRemoteID()
    {
        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();

        $locationCreateStruct = $locationService->newLocationCreateStruct( 2 );
        $locationCreateStruct->remoteId = '769380b7aa94541679167eab817ca893';
        $contentInfo = $contentService->loadContentInfo( 4 );
        $locationService->createLocation( $contentInfo, $locationCreateStruct );
    }

    /**
     * Test creating a location throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     */
    public function testCreateLocationThrowsInvalidArgumentExceptionParentIsASubLocation()
    {
        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();

        $locationCreateStruct = $locationService->newLocationCreateStruct( 44 );
        $contentInfo = $contentService->loadContentInfo( 4 );
        $locationService->createLocation( $contentInfo, $locationCreateStruct );
    }

    /**
     * Test updating location
     * @covers \eZ\Publish\API\Repository\LocationService::updateLocation
     */
    public function testUpdateLocation()
    {
        $locationService = $this->repository->getLocationService();

        $location = $locationService->loadLocation( 5 );
        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        $locationUpdateStruct->priority = 42;
        $locationUpdateStruct->sortField = Location::SORT_FIELD_DEPTH;
        $locationUpdateStruct->sortOrder = Location::SORT_ORDER_DESC;
        $locationUpdateStruct->remoteId = "NEW_REMOTE_ID";

        $location = $locationService->updateLocation( $location, $locationUpdateStruct );

        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $location );

        $this->assertPropertiesCorrect(
            array(
                'priority' => $locationUpdateStruct->priority,
                'sortField' => $locationUpdateStruct->sortField,
                'sortOrder' => $locationUpdateStruct->sortOrder,
                'remoteId' => $locationUpdateStruct->remoteId
            ),
            $location
        );
    }

    /**
     * Test updating location throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\LocationService::updateLocation
     */
    public function testUpdateLocationThrowsInvalidArgumentException()
    {
        $locationService = $this->repository->getLocationService();

        $location = $locationService->loadLocation( 5 );
        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        $locationUpdateStruct->remoteId = '769380b7aa94541679167eab817ca893';

        $locationService->updateLocation( $location, $locationUpdateStruct );
    }

    /**
     * Test swapping location
     * @covers \eZ\Publish\API\Repository\LocationService::swapLocation
     */
    public function testSwapLocation()
    {
        $locationService = $this->repository->getLocationService();

        $location1 = $locationService->loadLocation( 2 );
        $location2 = $locationService->loadLocation( 44 );

        $contentId1 = $location1->getContentInfo()->id;
        $contentId2 = $location2->getContentInfo()->id;

        $locationService->swapLocation( $location1, $location2 );

        $location1 = $locationService->loadLocation( 2 );
        $location2 = $locationService->loadLocation( 44 );

        self::assertEquals( $contentId1, $location2->getContentInfo()->id );
        self::assertEquals( $contentId2, $location1->getContentInfo()->id );
    }

    /**
     * Test hiding & unhiding a location
     * @covers \eZ\Publish\API\Repository\LocationService::hideLocation
     * @covers \eZ\Publish\API\Repository\LocationService::unhideLocation
     */
    public function testHideUnhideLocation()
    {
        $locationService = $this->repository->getLocationService();

        $location = $locationService->loadLocation( 5 );
        $location = $locationService->hideLocation( $location );
        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $location );
        self::assertEquals( true, $location->hidden );
        self::assertEquals( true, $location->invisible );

        $location = $locationService->unhideLocation( $location );
        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\Location', $location );
        self::assertEquals( false, $location->hidden );
        self::assertEquals( false, $location->invisible );
    }

    /**
     * Test moving a subtree
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     */
    public function testMoveSubtree()
    {
        $locationService = $this->repository->getLocationService();

        $locationToMove = $locationService->loadLocation( 13 );
        $newParent = $locationService->loadLocation( 44 );
        $locationService->moveSubtree( $locationToMove, $newParent );

        $loadedLocation = $locationService->loadLocation( $locationToMove->id );
        self::assertEquals( $newParent->id, $loadedLocation->parentLocationId );
    }

    /**
     * Test deleting a location
     * @covers \eZ\Publish\API\Repository\LocationService::deleteLocation
     */
    public function testDeleteLocation()
    {
        $locationService = $this->repository->getLocationService();

        $location = $locationService->loadLocation( 44 );
        $locationService->deleteLocation( $location );

        try
        {
            $locationService->loadLocation( $location->id );
            self::fail( "failed deleting a location" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * Test creating new LocationCreateStruct
     * @covers \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct
     */
    public function testNewLocationCreateStruct()
    {
        $locationService = $this->repository->getLocationService();

        $locationCreateStruct = $locationService->newLocationCreateStruct( 2 );
        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationCreateStruct', $locationCreateStruct );

        $this->assertPropertiesCorrect(
            array(
                'priority' => 0,
                'hidden' => false,
                'remoteId' => null,
                'sortField' => Location::SORT_FIELD_NAME,
                'sortOrder' => Location::SORT_ORDER_ASC,
                'parentLocationId' => 2
            ),
            $locationCreateStruct
        );
    }

    /**
     * Test creating new LocationUpdateStruct
     * @covers \eZ\Publish\API\Repository\LocationService::newLocationUpdateStruct
     */
    public function testNewLocationUpdateStruct()
    {
        $locationService = $this->repository->getLocationService();

        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\Content\\LocationUpdateStruct', $locationUpdateStruct );

        $this->assertPropertiesCorrect(
            array(
                'priority' => null,
                'remoteId' => null,
                'sortField' => null,
                'sortOrder' => null
            ),
            $locationUpdateStruct
        );
    }
}
