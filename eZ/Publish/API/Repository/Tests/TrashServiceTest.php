<?php
/**
 * File containing the TrashServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Values\Content\Location;
use \eZ\Publish\API\Repository\Values\Content\Query;
use \eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Test case for operations in the TrashService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\TrashService
 * @group integration
 * @group trash
 */
class TrashServiceTest extends BaseTrashServiceTest
{
    /**
     * Test for the trash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetTrashService
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationByRemoteId
     */
    public function testTrash()
    {
        /* BEGIN: Use Case */
        $trashItem = $this->createTrashItem();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\TrashItem',
            $trashItem
        );
    }

    /**
     * Test for the trash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testTrashSetsExpectedTrashItemProperties()
    {
        $repository = $this->getRepository();

        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        // Load the location that will be trashed
        $location = $repository->getLocationService()
            ->loadLocationByRemoteId( $mediaRemoteId );

        $expected = array(
            'id' => $location->id,
            'depth' => $location->depth,
            'hidden' => $location->hidden,
            'invisible' => $location->invisible,
            'parentLocationId' => $location->parentLocationId,
            'pathString' => $location->pathString,
            'priority' => $location->priority,
            'remoteId' => $location->remoteId,
            'sortField' => $location->sortField,
            'sortOrder' => $location->sortOrder,
        );

        $trashItem = $this->createTrashItem();

        $this->assertPropertiesCorrect( $expected, $trashItem );
    }

    /**
     * Test for the trash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testTrashRemovesLocationFromMainStorage()
    {
        $repository = $this->getRepository();

        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        /* BEGIN: Use Case */
        $this->createTrashItem();

        // Load the location service
        $locationService = $repository->getLocationService();

        // This call will fail with a "NotFoundException", because the media
        // location was marked as trashed in the main storage
        $locationService->loadLocationByRemoteId( $mediaRemoteId );
        /* END: Use Case */
    }

    /**
     * Test for the trash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testTrashRemovesChildLocationsFromMainStorage()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $remoteIds = $this->createRemoteIdList();

        $this->createTrashItem();

        // All invocations to loadLocationByRemoteId() to one of the above
        // collected remoteIds will return in an "NotFoundException"
        /* END: Use Case */

        $locationService = $repository->getLocationService();
        foreach ( $remoteIds as $remoteId )
        {
            try
            {
                $locationService->loadLocationByRemoteId( $remoteId );
                $this->fail( "Location '{$remoteId}' should exist.'" );
            }
            catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
            {
                // echo $e->getFile(), ' +', $e->getLine(), PHP_EOL;
            }
        }

        $this->assertGreaterThan(
            0,
            count( $remoteIds ),
            "There should be at least one 'Community' child location."
        );
    }

    /**
     * Test for the trash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testTrashDecrementsChildCountOnParentLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $baseLocationId = $this->generateId( 'location', 1 );

        $location = $locationService->loadLocation( $baseLocationId );

        $childCount = $locationService->getLocationChildCount( $location );

        $this->createTrashItem();

        $this->assertEquals(
            $childCount - 1,
            $locationService->getLocationChildCount( $location )
        );
    }

    /**
     * Test for the loadTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testLoadTrashItem()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $trashItem = $this->createTrashItem();

        // Reload the trash item
        $trashItemReloaded = $trashService->loadTrashItem( $trashItem->id );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\TrashItem',
            $trashItemReloaded
        );

        $this->assertEquals(
            $trashItem->pathString,
            $trashItemReloaded->pathString
        );
    }

    /**
     * Test for the loadTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testLoadTrashItem
     */
    public function testLoadTrashItemThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistingTrashId = $this->generateId( 'trash', 2342 );
        /* BEGIN: Use Case */
        $trashService = $repository->getTrashService();

        // This call will fail with a "NotFoundException", because no trash item
        // with the ID 1342 should exist in an eZ Publish demo installation
        $trashService->loadTrashItem( $nonExistingTrashId );
        /* END: Use Case */
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testRecover()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        /* BEGIN: Use Case */
        $trashItem = $this->createTrashItem();

        // Recover the trashed item
        $location = $trashService->recover( $trashItem );

        // Load the recovered location
        $locationReloaded = $locationService->loadLocationByRemoteId(
            $mediaRemoteId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\Location',
            $location
        );

        $this->assertEquals(
            $location->pathString,
            $locationReloaded->pathString
        );
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecover
     */
    public function testRecoverDoesNotRestoreChildLocations()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $remoteIds = $this->createRemoteIdList();

        // Unset remote ID of actually restored location
        unset( $remoteIds[array_search( '3f6d92f8044aed134f32153517850f5a', $remoteIds )] );

        $trashItem = $this->createTrashItem();

        $trashService->recover( $trashItem );

        $this->assertGreaterThan(
            0,
            count( $remoteIds ),
            "There should be at least one 'Community' child location."
        );

        // None of the child locations will be available again
        foreach ( $remoteIds as $remoteId )
        {
            try
            {
                $locationService->loadLocationByRemoteId( $remoteId );
                $this->fail(
                    sprintf(
                        'Location with remote ID "%s" unexpectedly restored.',
                        $remoteId
                    )
                );
            }
            catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
            {
                // All well
            }
        }
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem, $newParentLocation)
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecover
     * @todo Fix naming
     */
    public function testRecoverWithLocationCreateStructParameter()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $homeLocationId = $this->generateId( 'location', 2 );
        /* BEGIN: Use Case */
        // $homeLocationId is the ID of the "Home" location in an eZ Publish
        // demo installation

        $trashItem = $this->createTrashItem();

        // Get the new parent location
        $newParentLocation = $locationService->loadLocation( $homeLocationId );

        // Recover location with new location
        $location = $trashService->recover( $trashItem, $newParentLocation );
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            array(
                'remoteId' => $trashItem->remoteId,
                'parentLocationId' => $homeLocationId,
                // Not the full sub tree is restored
                'depth' => $newParentLocation->depth + 1,
                'hidden' => false,
                'invisible' => $trashItem->invisible,
                'pathString' => $newParentLocation->pathString . $this->parseId( 'location', $location->id ) . "/",
                'priority' => 0,
                'sortField' => Location::SORT_FIELD_NAME,
                'sortOrder' => Location::SORT_ORDER_ASC,
            ),
            $location
        );
    }

    /**
     * Test for the recover() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem, $newParentLocation)
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecoverWithLocationCreateStructParameter
     */
    public function testRecoverWithLocationCreateStructParameterIncrementsChildCountOnNewParent()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $homeLocationId = $this->generateId( 'location', 2 );

        $location = $locationService->loadLocation( $homeLocationId );

        $childCount = $locationService->getLocationChildCount( $location );

        /* BEGIN: Use Case */
        // $homeLocationId is the ID of the "Home" location in an eZ Publish
        // demo installation

        $trashItem = $this->createTrashItem();

        // Get the new parent location
        $newParentLocation = $locationService->loadLocation( $homeLocationId );

        // Recover location with new location
        $trashService->recover( $trashItem, $newParentLocation );
        /* END: Use Case */

        $this->assertEquals(
            $childCount + 1,
            $locationService->getLocationChildCount( $location )
        );
    }

    /**
     * Test for the findTrashItems() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::findTrashItems()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testFindTrashItems()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $this->createTrashItem();

        // Create a search query for all trashed items
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'title', Criterion\Operator::LIKE, '*' )
            )
        );

        // Load all trashed locations
        $searchResult = $trashService->findTrashItems( $query );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\Content\SearchResult',
            $searchResult
        );

        // 4 trashed locations from the sub tree
        $this->assertEquals( 4, $searchResult->count );
    }

    /**
     * Test for the emptyTrash() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::emptyTrash()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testFindTrashItems
     */
    public function testEmptyTrash()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $this->createTrashItem();

        // Empty the trash
        $trashService->emptyTrash();

        // Create a search query for all trashed items
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'title', Criterion\Operator::LIKE, '*' )
            )
        );

        // Load all trashed locations, search result should be empty
        $searchResult = $trashService->findTrashItems( $query );
        /* END: Use Case */

        $this->assertEquals( 0, $searchResult->count );
    }

    /**
     * Test for the deleteTrashItem() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\TrashService::deleteTrashItem()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testFindTrashItems
     */
    public function testDeleteTrashItem()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $demoDesignLocationId = $this->generateId( 'location', 56 );
        /* BEGIN: Use Case */
        // $demoDesignLocationId is the ID of the "Demo Design" location in an eZ
        // Publish demo installation

        $trashItem = $this->createTrashItem();

        // Trash one more location
        $trashService->trash(
            $locationService->loadLocation( $demoDesignLocationId )
        );

        // Empty the trash
        $trashService->deleteTrashItem( $trashItem );

        // Create a search query for all trashed items
        $query = new Query();
        $query->criterion = new Criterion\LogicalAnd(
            array(
                new Criterion\Field( 'title', Criterion\Operator::LIKE, '*' )
            )
        );

        // Load all trashed locations, should only contain the Demo Design location
        $searchResult = $trashService->findTrashItems( $query );
        /* END: Use Case */

        $foundIds = array_map(
            function ( $trashItem )
            {
                return $trashItem->id;
            },
            $searchResult->items
        );

        $this->assertEquals( 4, $searchResult->count );
        $this->assertTrue(
            array_search( $demoDesignLocationId, $foundIds ) !== false
        );
    }

    /**
     * Returns an array with the remoteIds of all child locations of the
     * <b>Community</b> location. It is stored in a local variable named
     * <b>$remoteIds</b>.
     *
     * @return string[]
     */
    private function createRemoteIdList()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        // remoteId of the "Community" location in an eZ Publish demo installation
        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        // Load the location service
        $locationService = $repository->getLocationService();

        $remoteIds = array();
        foreach (
            $locationService->loadLocationChildren(
                $locationService->loadLocationByRemoteId( $mediaRemoteId )
            )->locations as $child
        )
        {
            $remoteIds[] = $child->remoteId;
            foreach ( $locationService->loadLocationChildren( $child )->locations as $grandChild )
            {
                $remoteIds[] = $grandChild->remoteId;
            }
        }
        /* END: Inline */

        return $remoteIds;
    }
}
