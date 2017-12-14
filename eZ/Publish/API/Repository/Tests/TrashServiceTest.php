<?php

/**
 * File containing the TrashServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Trash\SearchResult;
use eZ\Publish\API\Repository\Values\Content\TrashItem as APITrashItem;
use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use eZ\Publish\Core\Repository\Values\Content\Location;

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
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationByRemoteId
     */
    public function testTrash()
    {
        /* BEGIN: Use Case */
        $trashItem = $this->createTrashItem();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\TrashItem',
            $trashItem
        );
    }

    /**
     * Test for the trash() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testTrashSetsExpectedTrashItemProperties()
    {
        $repository = $this->getRepository();

        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        // Load the location that will be trashed
        $location = $repository->getLocationService()
            ->loadLocationByRemoteId($mediaRemoteId);

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

        $this->assertPropertiesCorrect($expected, $trashItem);
    }

    /**
     * Test for the trash() method.
     *
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
        $locationService->loadLocationByRemoteId($mediaRemoteId);
        /* END: Use Case */
    }

    /**
     * Test for the trash() method.
     *
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
        foreach ($remoteIds as $remoteId) {
            try {
                $locationService->loadLocationByRemoteId($remoteId);
                $this->fail("Location '{$remoteId}' should exist.'");
            } catch (NotFoundException $e) {
                // echo $e->getFile(), ' +', $e->getLine(), PHP_EOL;
            }
        }

        $this->assertGreaterThan(
            0,
            count($remoteIds),
            "There should be at least one 'Community' child location."
        );
    }

    /**
     * Test for the trash() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::trash()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testTrashDecrementsChildCountOnParentLocation()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $baseLocationId = $this->generateId('location', 1);

        $location = $locationService->loadLocation($baseLocationId);

        $childCount = $locationService->getLocationChildCount($location);

        $this->createTrashItem();

        $this->refreshSearch($repository);

        $this->assertEquals(
            $childCount - 1,
            $locationService->getLocationChildCount($location)
        );
    }

    /**
     * Test sending a location to trash updates Content mainLocation.
     *
     * @covers \eZ\Publish\API\Repository\TrashService::trash
     */
    public function testTrashUpdatesMainLocation()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $trashService = $repository->getTrashService();

        $contentInfo = $contentService->loadContentInfo(42);

        // Create additional location that will become new main location
        $location = $locationService->createLocation(
            $contentInfo,
            new LocationCreateStruct(['parentLocationId' => 2])
        );

        $trashService->trash(
            $locationService->loadLocation($contentInfo->mainLocationId)
        );

        self::assertEquals(
            $location->id,
            $contentService->loadContentInfo(42)->mainLocationId
        );
    }

    /**
     * Test sending a location to trash.
     *
     * @covers \eZ\Publish\API\Repository\TrashService::trash
     */
    public function testTrashReturnsNull()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $trashService = $repository->getTrashService();

        // Create additional location to trash
        $location = $locationService->createLocation(
            $contentService->loadContentInfo(42),
            new LocationCreateStruct(['parentLocationId' => 2])
        );

        $trashItem = $trashService->trash($location);

        self::assertNull($trashItem);
    }

    /**
     * Test for the loadTrashItem() method.
     *
     * @covers \eZ\Publish\API\Repository\TrashService::loadTrashItem
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testLoadTrashItem()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $trashItem = $this->createTrashItem();

        // Reload the trash item
        $trashItemReloaded = $trashService->loadTrashItem($trashItem->id);
        /* END: Use Case */

        $this->assertInstanceOf(
            APITrashItem::class,
            $trashItemReloaded
        );

        $this->assertEquals(
            $trashItem->pathString,
            $trashItemReloaded->pathString
        );

        $this->assertEquals(
            $trashItem,
            $trashItemReloaded
        );
    }

    /**
     * Test for the loadTrashItem() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testLoadTrashItem
     */
    public function testLoadTrashItemThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistingTrashId = $this->generateId('trash', 2342);
        /* BEGIN: Use Case */
        $trashService = $repository->getTrashService();

        // This call will fail with a "NotFoundException", because no trash item
        // with the ID 1342 should exist in an eZ Publish demo installation
        $trashService->loadTrashItem($nonExistingTrashId);
        /* END: Use Case */
    }

    /**
     * Test for the recover() method.
     *
     * @covers \eZ\Publish\API\Repository\TrashService::recover
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
        $location = $trashService->recover($trashItem);

        // Load the recovered location
        $locationReloaded = $locationService->loadLocationByRemoteId(
            $mediaRemoteId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            APILocation::class,
            $location
        );

        $this->assertEquals(
            $location,
            $locationReloaded
        );

        try {
            $trashService->loadTrashItem($trashItem->id);
            $this->fail('Trash item was not removed after being recovered.');
        } catch (NotFoundException $e) {
            // All well
        }
    }

    /**
     * Test recovering a non existing trash item results in a NotFoundException.
     *
     * @covers \eZ\Publish\API\Repository\TrashService::recover
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testRecoverThrowsNotFoundExceptionForNonExistingTrashItem()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        $trashItem = new TrashItem(['id' => 12364, 'parentLocationId' => 12363]);
        $trashService->recover($trashItem);
    }

    /**
     * Test for the trash() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testNotFoundAliasAfterRemoveIt()
    {
        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        // Double ->lookup() call because there where issue that one call was not enough to spot bug
        $urlAliasService->lookup('/Media');
        $urlAliasService->lookup('/Media');

        $mediaLocation = $locationService->loadLocationByRemoteId($mediaRemoteId);
        $trashService->trash($mediaLocation);

        $urlAliasService->lookup('/Media');
    }

    /**
     * Test for the recover() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testTrash
     */
    public function testAliasesForRemovedItems()
    {
        $mediaRemoteId = '75c715a51699d2d309a924eca6a95145';

        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        // Double ->lookup() call because there where issue that one call was not enough to spot bug
        $urlAliasService->lookup('/Media');
        $trashedLocationAlias = $urlAliasService->lookup('/Media');

        $mediaLocation = $locationService->loadLocationByRemoteId($mediaRemoteId);
        $trashItem = $trashService->trash($mediaLocation);
        $this->assertAliasNotExists($urlAliasService, '/Media');

        $this->createNewContentInPlaceTrashedOne($repository, $mediaLocation->parentLocationId);

        $createdLocationAlias = $urlAliasService->lookup('/Media');

        $this->assertNotEquals(
            $trashedLocationAlias->destination,
            $createdLocationAlias->destination,
            'Destination for /media url should changed'
        );

        $recoveredLocation = $trashService->recover($trashItem);
        $recoveredLocationAlias = $urlAliasService->lookup('/Media2');
        $recoveredLocationAliasReverse = $urlAliasService->reverseLookup($recoveredLocation);

        $this->assertEquals($recoveredLocationAlias->destination, $recoveredLocationAliasReverse->destination);

        $this->assertNotEquals($recoveredLocationAliasReverse->destination, $trashedLocationAlias->destination);
        $this->assertNotEquals($recoveredLocationAliasReverse->destination, $createdLocationAlias->destination);
    }

    /**
     * Test for the recover() method.
     *
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
        unset($remoteIds[array_search('3f6d92f8044aed134f32153517850f5a', $remoteIds)]);

        $trashItem = $this->createTrashItem();

        $trashService->recover($trashItem);

        $this->assertGreaterThan(
            0,
            count($remoteIds),
            "There should be at least one 'Community' child location."
        );

        // None of the child locations will be available again
        foreach ($remoteIds as $remoteId) {
            try {
                $locationService->loadLocationByRemoteId($remoteId);
                $this->fail(
                    sprintf(
                        'Location with remote ID "%s" unexpectedly restored.',
                        $remoteId
                    )
                );
            } catch (NotFoundException $e) {
                // All well
            }
        }

        try {
            $trashService->loadTrashItem($trashItem->id);
            $this->fail('Trash item was not removed after being recovered.');
        } catch (NotFoundException $e) {
            // All well
        }
    }

    /**
     * Test for the recover() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem, $newParentLocation)
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecover
     *
     * @todo Fix naming
     */
    public function testRecoverWithLocationCreateStructParameter()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $homeLocationId = $this->generateId('location', 2);
        /* BEGIN: Use Case */
        // $homeLocationId is the ID of the "Home" location in an eZ Publish
        // demo installation

        $trashItem = $this->createTrashItem();

        // Get the new parent location
        $newParentLocation = $locationService->loadLocation($homeLocationId);

        // Recover location with new location
        $location = $trashService->recover($trashItem, $newParentLocation);
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            array(
                'remoteId' => $trashItem->remoteId,
                'parentLocationId' => $homeLocationId,
                // Not the full sub tree is restored
                'depth' => $newParentLocation->depth + 1,
                'hidden' => false,
                'invisible' => $trashItem->invisible,
                'pathString' => $newParentLocation->pathString . $this->parseId('location', $location->id) . '/',
                'priority' => 0,
                'sortField' => APILocation::SORT_FIELD_NAME,
                'sortOrder' => APILocation::SORT_ORDER_ASC,
            ),
            $location
        );

        try {
            $trashService->loadTrashItem($trashItem->id);
            $this->fail('Trash item was not removed after being recovered.');
        } catch (NotFoundException $e) {
            // All well
        }
    }

    /**
     * Test for the recover() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem)
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecover
     */
    public function testRecoverIncrementsChildCountOnOriginalParent()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($this->generateId('location', 1));

        $trashItem = $this->createTrashItem();

        $this->refreshSearch($repository);

        /* BEGIN: Use Case */
        $childCount = $locationService->getLocationChildCount($location);

        // Recover location with new location
        $trashService->recover($trashItem);
        /* END: Use Case */

        $this->refreshSearch($repository);

        $this->assertEquals(
            $childCount + 1,
            $locationService->getLocationChildCount($location)
        );

        try {
            $trashService->loadTrashItem($trashItem->id);
            $this->fail('Trash item was not removed after being recovered.');
        } catch (NotFoundException $e) {
            // All well
        }
    }

    /**
     * Test for the recover() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem, $newParentLocation)
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecoverWithLocationCreateStructParameter
     */
    public function testRecoverWithLocationCreateStructParameterIncrementsChildCountOnNewParent()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $homeLocationId = $this->generateId('location', 2);

        $location = $locationService->loadLocation($homeLocationId);

        $childCount = $locationService->getLocationChildCount($location);

        /* BEGIN: Use Case */
        // $homeLocationId is the ID of the "Home" location in an eZ Publish
        // demo installation

        $trashItem = $this->createTrashItem();

        // Get the new parent location
        $newParentLocation = $locationService->loadLocation($homeLocationId);

        // Recover location with new location
        $trashService->recover($trashItem, $newParentLocation);
        /* END: Use Case */

        $this->refreshSearch($repository);

        $this->assertEquals(
            $childCount + 1,
            $locationService->getLocationChildCount($location)
        );

        try {
            $trashService->loadTrashItem($trashItem->id);
            $this->fail('Trash item was not removed after being recovered.');
        } catch (NotFoundException $e) {
            // All well
        }
    }

    /**
     * Test recovering a location from trash to non existing location.
     *
     * @covers \eZ\Publish\API\Repository\TrashService::recover
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testRecoverToNonExistingLocation()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation(44);
        $trashItem = $trashService->trash($location);

        $newParentLocation = new Location(
            array(
                'id' => 123456,
                'parentLocationId' => 123455,
            )
        );
        $trashService->recover($trashItem, $newParentLocation);
    }

    /**
     * Test for the findTrashItems() method.
     *
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
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\Field('title', Criterion\Operator::LIKE, '*'),
            )
        );

        // Load all trashed locations
        $searchResult = $trashService->findTrashItems($query);
        /* END: Use Case */

        $this->assertInstanceOf(
            SearchResult::class,
            $searchResult
        );

        // 4 trashed locations from the sub tree
        $this->assertEquals(4, $searchResult->count);
        $this->assertEquals(4, $searchResult->totalCount);
    }

    /**
     * Test for the findTrashItems() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::findTrashItems()
     * @depends \eZ\Publish\API\Repository\Tests\TrashServiceTest::testFindTrashItems
     */
    public function testFindTrashItemsLimitedAccess()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        /* BEGIN: Use Case */
        $this->createTrashItem();

        // Create a search query for all trashed items
        $query = new Query();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\Field('title', Criterion\Operator::LIKE, '*'),
            )
        );

        // Create a user in the Editor user group.
        $user = $this->createUserVersion1();

        // Set the Editor user as current user, these users have no access to Trash by default.
        $repository->getPermissionResolver()->setCurrentUserReference($user);

        // Load all trashed locations
        $searchResult = $trashService->findTrashItems($query);
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\Content\\SearchResult',
            $searchResult
        );

        // 0 trashed locations found, though 4 exist
        $this->assertEquals(0, $searchResult->count);
    }

    /**
     * Test for the emptyTrash() method.
     *
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
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\Field('title', Criterion\Operator::LIKE, '*'),
            )
        );

        // Load all trashed locations, search result should be empty
        $searchResult = $trashService->findTrashItems($query);
        /* END: Use Case */

        $this->assertEquals(0, $searchResult->count);
    }

    /**
     * Test for the deleteTrashItem() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::deleteTrashItem()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testFindTrashItems
     */
    public function testDeleteTrashItem()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $demoDesignLocationId is the ID of the "Demo Design" location in an eZ
        // Publish demo installation

        $trashItem = $this->createTrashItem();

        // Trash one more location
        $trashService->trash(
            $locationService->loadLocation($demoDesignLocationId)
        );

        // Empty the trash
        $trashService->deleteTrashItem($trashItem);

        // Create a search query for all trashed items
        $query = new Query();
        $query->filter = new Criterion\LogicalAnd(
            array(
                new Criterion\Field('title', Criterion\Operator::LIKE, '*'),
            )
        );

        // Load all trashed locations, should only contain the Demo Design location
        $searchResult = $trashService->findTrashItems($query);
        /* END: Use Case */

        $foundIds = array_map(
            function ($trashItem) {
                return $trashItem->id;
            },
            $searchResult->items
        );

        $this->assertEquals(4, $searchResult->count);
        $this->assertTrue(
            in_array($demoDesignLocationId, $foundIds)
        );
    }

    /**
     * Test deleting a non existing trash item.
     *
     * @covers \eZ\Publish\API\Repository\TrashService::deleteTrashItem
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteThrowsNotFoundExceptionForNonExistingTrashItem()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        $trashItem = new TrashItem(['id' => 123456]);
        $trashService->deleteTrashItem($trashItem);
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
        $children = $locationService->loadLocationChildren($locationService->loadLocationByRemoteId($mediaRemoteId));
        foreach ($children->locations as $child) {
            $remoteIds[] = $child->remoteId;
            foreach ($locationService->loadLocationChildren($child)->locations as $grandChild) {
                $remoteIds[] = $grandChild->remoteId;
            }
        }
        /* END: Inline */

        return $remoteIds;
    }

    /**
     * @param Repository $repository
     * @param int $parentLocationId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function createNewContentInPlaceTrashedOne(Repository $repository, $parentLocationId)
    {
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier('forum');
        $newContent = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $newContent->setField('name', 'Media');

        $location = $locationService->newLocationCreateStruct($parentLocationId);

        $draftContent = $contentService->createContent($newContent, [$location]);

        return $contentService->publishVersion($draftContent->versionInfo);
    }

    /**
     * @param URLAliasService $urlAliasService
     * @param string $urlPath Url alias path
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    private function assertAliasExists(URLAliasService $urlAliasService, $urlPath)
    {
        $urlAlias = $urlAliasService->lookup($urlPath);

        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\Content\URLAlias', $urlAlias);

        return $urlAlias;
    }

    /**
     * @param URLAliasService $urlAliasService
     * @param string $urlPath Url alias path
     */
    private function assertAliasNotExists(URLAliasService $urlAliasService, $urlPath)
    {
        try {
            $this->getRepository()->getURLAliasService()->lookup($urlPath);
            $this->fail(sprintf('Alias [%s] should not exists', $urlPath));
        } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            $this->assertTrue(true);
        }
    }
}
