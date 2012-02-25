<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\LocationBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;

use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\Core\Repository\Values\Content\Location,

    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission,
    eZ\Publish\API\Repository\Exceptions\NotFoundException,
    eZ\Publish\API\Repository\Exceptions\IllegalArgumentException;

/**
 * Test case for Location Service
 *
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
        self::assertNull( $location->id );
        self::assertNull( $location->priority );
        self::assertNull( $location->hidden );
        self::assertNull( $location->invisible );
        self::assertNull( $location->remoteId );
        self::assertNull( $location->parentLocationId );
        self::assertNull( $location->pathString );
        self::assertNull( $location->modifiedSubLocationDate );
        self::assertNull( $location->depth );
        self::assertNull( $location->sortField );
        self::assertNull( $location->sortOrder );
        self::assertNull( $location->childrenCount );
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
        catch( PropertyNotFound $e ) {}
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
        catch( PropertyPermission $e ) {}
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
        catch ( PropertyPermission $e ) {}
    }

    /**
     * Test copying a subtree
     * @covers \eZ\Publish\API\Repository\LocationService::copySubtree
     */
    public function testCopySubtree()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();
        $locationToCopy = $locationService->loadLocation( 5 );
        $targetLocation = $locationService->loadLocation( 2 );

        $copiedSubtree = $locationService->copySubtree( $locationToCopy, $targetLocation );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $copiedSubtree );
        self::assertGreaterThan( 0, $copiedSubtree->id );
        self::assertEquals( $targetLocation->id, $copiedSubtree->parentLocationId );
    }

    /**
     * Test copying a subtree throwing IllegalArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @covers \eZ\Publish\API\Repository\LocationService::copySubtree
     */
    public function testCopySubtreeThrowsIllegalArgumentException()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
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
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();
        $loadedLocation = $locationService->loadLocation( 2 );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $loadedLocation );
        self::assertEquals( 2, $loadedLocation->id );
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
        self::markTestSkipped( "@todo: enable when LocationService::loadLocationByRemoteId is implemented" );
        $location = $this->repository->getLocationService()->loadLocationByRemoteId( "f3e90596361e31d496d4026eb624c983" );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $location );
        self::assertGreaterThan( 0, $location->id );
        self::assertEquals( "f3e90596361e31d496d4026eb624c983", $location->remoteId );
    }

    /**
     * Test loading main location
     * @covers \eZ\Publish\API\Repository\LocationService::loadMainLocation
     */
    public function testLoadMainLocation()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $contentInfo = $this->repository->getContentService()->loadContentInfo( 65 );
        $location = $this->repository->getLocationService()->loadMainLocation( $contentInfo );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $location );
        self::assertGreaterThan( 0, $location->id );
        self::assertEquals( true, $location->id == $location->getContentInfo()->mainLocationId );
    }

    /**
     * Test loading main location throwing BadStateException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @covers \eZ\Publish\API\Repository\LocationService::loadMainLocation
     */
    public function testLoadMainLocationThrowsBadStateException()
    {
        self::markTestIncomplete( "@todo: implement when content service is implemented" );
    }

    /**
     * Test loading locations for content
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocations
     */
    public function testLoadLocations()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $contentInfo = $this->repository->getContentService()->loadContentInfo( 4 );

        $locationService = $this->repository->getLocationService();
        $locations = $locationService->loadLocations( $contentInfo );

        self::assertInternalType( "array", $locations );
        self::assertNotEmpty( $locations );

        foreach ( $locations as $location )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $location );
        }

        $locationsCount = count( $locations );

        $locationCreateStruct = $locationService->newLocationCreateStruct( 43 );
        $locationService->createLocation( $contentInfo, $locationCreateStruct );

        $locations = $locationService->loadLocations( $contentInfo );

        self::assertInternalType( "array", $locations );
        self::assertNotEmpty( $locations );

        foreach ( $locations as $location )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $location );
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
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $contentInfo = $this->repository->getContentService()->loadContentInfo( 4 );

        $locationService = $this->repository->getLocationService();
        $parentLocation = $locationService->loadLocation( 43 );

        $locationCreateStruct = $locationService->newLocationCreateStruct( $parentLocation->id );
        $locationService->createLocation( $contentInfo, $locationCreateStruct );

        $locations = $locationService->loadLocations( $contentInfo, $parentLocation );

        self::assertInternalType( "array", $locations );
        self::assertNotEmpty( $locations );

        foreach ( $locations as $location )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $location );
        }

        foreach ( $locations as $location )
        {
            /** @var $location \eZ\Publish\API\Repository\Values\Content\Location */
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
        self::markTestIncomplete( "@todo: implement when content service is implemented" );
    }

    /**
     * Test loading location children
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationChildren
     */
    public function testLoadLocationChildren()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );

        $locationService = $this->repository->getLocationService();

        $rootLocation = $locationService->loadLocation( 2 );
        $childrenLocations = $locationService->loadLocationChildren( $rootLocation );

        self::assertInternalType( "array", $childrenLocations );
        self::assertNotEmpty( $childrenLocations );

        foreach ( $childrenLocations as $childLocation )
        {
            self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $childLocation );
        }
    }

    /**
     * Test creating a location
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     */
    public function testCreateLocation()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();

        $locationCreateStruct = $locationService->newLocationCreateStruct( 52 );
        $content = $contentService->loadContent( 49 );

        $createdLocation = $locationService->createLocation( $content->getVersionInfo()->getContentInfo(), $locationCreateStruct );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $createdLocation );
        self::assertGreaterThan( 0, $createdLocation->id );
        self::assertEquals( $content->getVersionInfo()->getContentInfo()->contentId, $createdLocation->getContentInfo()->contentId );
        self::assertEquals( $locationCreateStruct->parentLocationId, $createdLocation->parentLocationId );
    }

    /**
     * Test creating a location throwing IllegalArgumentException
     * @covers \eZ\Publish\API\Repository\LocationService::createLocation
     */
    public function testCreateLocationThrowsIllegalArgumentException()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();
        $contentService = $this->repository->getContentService();

        try
        {
            $locationCreateStruct = $locationService->newLocationCreateStruct( 43 );
            $content = $contentService->loadContent( 49 );
            $locationService->createLocation( $content->getVersionInfo()->getContentInfo(), $locationCreateStruct );
            self::fail( "succeeded adding a location to content where content already has a location below specified parent" );
        }
        catch ( IllegalArgumentException $e ) {}

        try
        {
            $locationCreateStruct = $locationService->newLocationCreateStruct( 52 );
            $locationCreateStruct->remoteId = "4fdf0072da953bb276c0c7e0141c5c9b";
            $content = $contentService->loadContent( 49 );
            $locationService->createLocation( $content->getVersionInfo()->getContentInfo(), $locationCreateStruct );
            self::fail( "succeeded adding a location with existing remote ID" );
        }
        catch ( IllegalArgumentException $e ) {}

        try
        {
            $locationCreateStruct = $locationService->newLocationCreateStruct( 52 );
            $content = $contentService->loadContent( 41 );
            $locationService->createLocation( $content->getVersionInfo()->getContentInfo(), $locationCreateStruct );
            self::fail( "succeeded adding a location where parent is a sub location of the content" );
        }
        catch ( IllegalArgumentException $e ) {}
    }

    /**
     * Test updating location
     * @covers \eZ\Publish\API\Repository\LocationService::updateLocation
     */
    public function testUpdateLocation()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();

        $location = $locationService->loadLocation( 5 );
        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        $locationUpdateStruct->remoteId = "NEW_REMOTE_ID";

        $location = $locationService->updateLocation( $location, $locationUpdateStruct );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $location );
        self::assertEquals( $locationUpdateStruct->remoteId, $location->remoteId );
    }

    /**
     * Test updating location throwing IllegalArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @covers \eZ\Publish\API\Repository\LocationService::updateLocation
     */
    public function testUpdateLocationThrowsIllegalArgumentException()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();

        $location = $locationService->loadLocation( 5 );
        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        $locationUpdateStruct->remoteId = "4fdf0072da953bb276c0c7e0141c5c9b";

        $locationService->updateLocation( $location, $locationUpdateStruct );
    }

    /**
     * Test swapping location
     * @covers \eZ\Publish\API\Repository\LocationService::swapLocation
     */
    public function testSwapLocation()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();

        $location1 = $locationService->loadLocation( 13 );
        $location2 = $locationService->loadLocation( 14 );

        $contentId1 = $location1->getContentInfo()->contentId;
        $contentId2 = $location2->getContentInfo()->contentId;

        $locationService->swapLocation( $location1, $location2 );

        $location1 = $locationService->loadLocation( 13 );
        $location2 = $locationService->loadLocation( 14 );

        self::assertEquals( $contentId1, $location2->getContentInfo()->contentId );
        self::assertEquals( $contentId2, $location1->getContentInfo()->contentId );
    }

    /**
     * Test hiding & unhiding a location
     * @covers \eZ\Publish\API\Repository\LocationService::hideLocation
     * @covers \eZ\Publish\API\Repository\LocationService::unhideLocation
     */
    public function testHideUnhideLocation()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();

        $location = new Location( array( "id" => 5 ) );
        $location = $locationService->hideLocation( $location );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $location );
        self::assertEquals( true, $location->hidden );
        self::assertEquals( true, $location->invisible );

        $location = $locationService->unhideLocation( $location );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $location );
        self::assertEquals( false, $location->hidden );
        self::assertEquals( false, $location->invisible );
    }

    /**
     * Test moving a subtree
     * @covers \eZ\Publish\API\Repository\LocationService::moveSubtree
     */
    public function testMoveSubtree()
    {
        self::markTestSkipped( "@todo: enable when content service is implemented" );
        $locationService = $this->repository->getLocationService();

        $locationToMove = new Location( array( "id" => 5 ) );
        $newParent = new Location( array( "id" => 2 ) );
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
        self::markTestSkipped( "@todo: enable when method removeSubtree is implemented in persistence" );
        $locationService = $this->repository->getLocationService();

        $location = new Location( array( "id" => 43 ) );
        $locationService->deleteLocation( $location );

        try
        {
            $locationService->loadLocation( $location->id );
            self::fail( "failed deleting a location" );
        }
        catch ( NotFoundException $e ) {}
    }

    /**
     * Test creating new LocationCreateStruct
     * @covers \eZ\Publish\API\Repository\LocationService::newLocationCreateStruct
     */
    public function testNewLocationCreateStruct()
    {
        $locationService = $this->repository->getLocationService();

        $locationCreateStruct = $locationService->newLocationCreateStruct( 2 );
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\LocationCreateStruct', $locationCreateStruct );
        self::assertEquals( 0, $locationCreateStruct->priority );
        self::assertEquals( false, $locationCreateStruct->hidden );
        self::assertNull( $locationCreateStruct->remoteId );
        self::assertEquals( false, $locationCreateStruct->isMainLocation );
        self::assertEquals( Location::SORT_FIELD_NAME, $locationCreateStruct->sortField );
        self::assertEquals( Location::SORT_ORDER_ASC, $locationCreateStruct->sortOrder );
        self::assertEquals( 2, $locationCreateStruct->parentLocationId );
    }

    /**
     * Test creating new LocationUpdateStruct
     * @covers \eZ\Publish\API\Repository\LocationService::newLocationUpdateStruct
     */
    public function testNewLocationUpdateStruct()
    {
        $locationService = $this->repository->getLocationService();

        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct', $locationUpdateStruct );
        self::assertNull( $locationUpdateStruct->priority );
        self::assertNull( $locationUpdateStruct->remoteId );
        self::assertNull( $locationUpdateStruct->sortField );
        self::assertNull( $locationUpdateStruct->sortOrder );
    }
}
