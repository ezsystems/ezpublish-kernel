<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\TrashBase class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;

use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\Core\Repository\Values\Content\TrashItem,
    eZ\Publish\API\Repository\Values\Content\Query,

    eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound,
    eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException,
    eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for Trash Service
 *
 */
abstract class TrashBase extends BaseServiceTest
{
    /**
     * Test a new class and default values on properties
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::__construct
     */
    public function testNewClass()
    {
        $trashItem = new TrashItem();

        $this->assertPropertiesCorrect(
            array(
                'id'                      => null,
                'priority'                => null,
                'hidden'                  => null,
                'invisible'               => null,
                'remoteId'                => null,
                'parentLocationId'        => null,
                'pathString'              => null,
                'modifiedSubLocationDate' => null,
                'depth'                   => null,
                'sortField'               => null,
                'sortOrder'               => null,
                'childCount'              => null
            ),
            $trashItem
        );
    }

    /**
     * Test retrieving missing property
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $trashItem = new TrashItem();
            $value = $trashItem->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch( PropertyNotFound $e ) {}
    }

    /**
     * Test setting read only property
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $trashItem = new TrashItem();
            $trashItem->id = 42;
            self::fail( "Succeeded setting read only property" );
        }
        catch( PropertyReadOnlyException $e ) {}
    }

    /**
     * Test if property exists
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::__isset
     */
    public function testIsPropertySet()
    {
        $trashItem = new TrashItem();
        $value = isset( $trashItem->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $trashItem->id );
        self::assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::__unset
     */
    public function testUnsetProperty()
    {
        $trashItem = new TrashItem( array( "id" => 2 ) );
        try
        {
            unset( $trashItem->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e ) {}
    }

    /**
     * Test loading a trash item
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::loadTrashItem
     */
    public function testLoadTrashItem()
    {
        $locationService = $this->repository->getLocationService();
        $trashService = $this->repository->getTrashService();

        $trashItem = $location = $trashService->trash( $locationService->loadLocation( 44 ) );
        $loadedTrashItem = $trashService->loadTrashItem( $location->id );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\TrashItem', $trashItem );

        self::assertSameClassPropertiesCorrect(
            array(
                'id',
                'priority',
                'hidden',
                'invisible',
                'remoteId',
                'parentLocationId',
                'pathString',
                'modifiedSubLocationDate',
                'depth',
                'sortField',
                'sortOrder',
                'childCount'
            ),
            $trashItem,
            $loadedTrashItem
        );
    }

    /**
     * Test loading a trash item throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::loadTrashItem
     */
    public function testLoadTrashItemThrowsNotFoundException()
    {
        $trashService = $this->repository->getTrashService();
        $trashService->loadTrashItem( 44 );
    }

    /**
     * Test sending a location to trash
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::trash
     */
    public function testTrash()
    {
        $locationService = $this->repository->getLocationService();
        $trashService = $this->repository->getTrashService();

        $location = $locationService->loadLocation( 44 );
        $trashItem = $trashService->trash( $location );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\TrashItem', $trashItem );

        self::assertSameClassPropertiesCorrect(
            array(
                'id',
                'priority',
                'hidden',
                'invisible',
                'remoteId',
                'parentLocationId',
                'pathString',
                'modifiedSubLocationDate',
                'depth',
                'sortField',
                'sortOrder'
            ),
            $location,
            $trashItem,
            //@todo: enable the following properties
            array( 'childCount' )
        );
    }

    /**
     * Test recovering a location from trash to original location
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::recover
     */
    public function testRecover()
    {
        $locationService = $this->repository->getLocationService();
        $trashService = $this->repository->getTrashService();

        $location = $locationService->loadLocation( 44 );
        $trashItem = $trashService->trash( $location );

        $recoveredLocation = $trashService->recover( $trashItem );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $recoveredLocation );

        self::assertSameClassPropertiesCorrect(
            array(
                'priority',
                'hidden',
                'invisible',
                'remoteId',
                'parentLocationId',
                'depth',
                'sortField',
                'sortOrder'
            ),
            $location,
            $recoveredLocation,
            //@todo: assert child count
            array( 'childCount' )
        );

        $parentLocation = $locationService->loadLocation( $location->parentLocationId );
        $newPathString = $parentLocation->pathString . $recoveredLocation->id . '/';

        self::assertEquals( $newPathString, $recoveredLocation->pathString );
        self::assertGreaterThan( 0, $recoveredLocation->id );
    }

    /**
     * Test recovering a non existing trash item
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::recover
     */
    public function testRecoverNonExistingTrashItem()
    {
        $trashService = $this->repository->getTrashService();

        $trashItem = new TrashItem( array( "id" => PHP_INT_MAX, "parentLocationId" => PHP_INT_MAX ) );
        $trashService->recover( $trashItem );
    }

    /**
     * Test recovering a location from trash to different location
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::recover
     */
    public function testRecoverToDifferentLocation()
    {
        $locationService = $this->repository->getLocationService();
        $trashService = $this->repository->getTrashService();

        $location = $locationService->loadLocation( 44 );
        $trashItem = $trashService->trash( $location );

        $locationCreateStruct = $locationService->newLocationCreateStruct( 2 );

        $recoveredLocation = $trashService->recover( $trashItem, $locationCreateStruct );

        self::assertInstanceOf( '\eZ\Publish\API\Repository\Values\Content\Location', $recoveredLocation );

        self::assertSameClassPropertiesCorrect(
            array(
                'priority',
                'hidden',
                'invisible',
                'remoteId',
                'depth',
                'sortField',
                'sortOrder'
            ),
            $location,
            $recoveredLocation,
            //@todo: assert child count
            array( 'childCount' )
        );

        $parentLocation = $locationService->loadLocation( $recoveredLocation->parentLocationId );
        $newPathString = $parentLocation->pathString . $recoveredLocation->id . '/';

        self::assertEquals( $newPathString, $recoveredLocation->pathString );
        self::assertGreaterThan( 0, $recoveredLocation->id );
        self::assertEquals( $locationCreateStruct->parentLocationId, $recoveredLocation->parentLocationId );
    }

    /**
     * Test recovering a location from trash to non existing location
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::recover
     */
    public function testRecoverToNonExistingLocation()
    {
        $locationService = $this->repository->getLocationService();
        $trashService = $this->repository->getTrashService();

        $location = $locationService->loadLocation( 44 );
        $trashItem = $trashService->trash( $location );

        $locationCreateStruct = $locationService->newLocationCreateStruct( PHP_INT_MAX );
        $trashService->recover( $trashItem, $locationCreateStruct );
    }

    /**
     * Test deleting a trash item
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::deleteTrashItem
     */
    public function testDeleteTrashItem()
    {
        $locationService = $this->repository->getLocationService();
        $trashService = $this->repository->getTrashService();

        $location = $locationService->loadLocation( 44 );
        $trashItem = $trashService->trash( $location );

        $trashService->deleteTrashItem( $trashItem );

        try
        {
            $trashService->loadTrashItem( $trashItem->id );
            self::fail( "Succeeded loading deleted trash item" );
        }
        catch ( NotFoundException $e ) {}
    }

    /**
     * Test deleting a non existing trash item
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::deleteTrashItem
     */
    public function testDeleteNonExistingTrashItem()
    {
        $trashService = $this->repository->getTrashService();

        $trashItem = new TrashItem( array( "id" => PHP_INT_MAX ) );
        $trashService->deleteTrashItem( $trashItem );
    }

    /**
     * Test searching for trash items
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::findTrashItems
     * @covers \eZ\Publish\API\Repository\Values\Content\TrashItem::emptyTrash
     */
    public function testFindTrashItemsAndEmptyTrash()
    {
        $locationService = $this->repository->getLocationService();
        $trashService = $this->repository->getTrashService();

        $searchResult = $trashService->findTrashItems( new Query() );
        $countBeforeTrashing = $searchResult->count;

        $location = $locationService->loadLocation( 5 );
        $trashService->trash( $location );

        $searchResult = $trashService->findTrashItems( new Query() );
        $countAfterTrashing = $searchResult->count;

        self::assertGreaterThan( $countBeforeTrashing, $countAfterTrashing );

        $trashService->emptyTrash();
        $searchResult = $trashService->findTrashItems( new Query() );

        self::assertEquals( 0, $searchResult->count );
    }
}
