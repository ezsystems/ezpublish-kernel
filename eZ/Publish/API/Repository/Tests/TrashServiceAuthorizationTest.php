<?php

/**
 * File containing the TrashServiceAuthorizationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\Core\Repository\Repository;
use eZ\Publish\Core\Repository\TrashService;

/**
 * Test case for operations in the TrashService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\TrashService
 * @group integration
 * @group authorization
 */
class TrashServiceAuthorizationTest extends BaseTrashServiceTest
{
    /**
     * Test for the loadTrashItem() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::loadTrashItem()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testLoadTrashItem
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUser
     */
    public function testLoadTrashItemThrowsUnauthorizedException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user
        $trashItem = $this->createTrashItem();

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with an "UnauthorizedException"
        $trashService->loadTrashItem($trashItem->id);
        /* END: Use Case */
    }

    /**
     * Test for the trash() method without proper permissions.
     *
     * @covers \eZ\Publish\API\Repository\TrashService::trash
     */
    public function testTrashThrowsUnauthorizedException()
    {
        $this->expectException(\eZ\Publish\Core\Base\Exceptions\UnauthorizedException::class);
        $this->expectExceptionMessage('User does not have access to \'remove\' \'content\'');

        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        // Load "Media" page location to be trashed
        $mediaLocation = $locationService->loadLocationByRemoteId(
            '75c715a51699d2d309a924eca6a95145'
        );

        // switch user context before testing TrashService::trash method
        $repository->getPermissionResolver()->setCurrentUserReference(
            $this->createUserWithPolicies('trash_test_user', [])
        );
        $trashService->trash($mediaLocation);
    }

    /**
     * Test for the trash() method with proper minimal permission set.
     *
     * @depends testTrashThrowsUnauthorizedException
     *
     * @covers \eZ\Publish\API\Repository\TrashService::trash
     */
    public function testTrashRequiresContentRemovePolicy()
    {
        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        // Load "Media" page location to be trashed
        $mediaLocation = $locationService->loadLocationByRemoteId(
            '75c715a51699d2d309a924eca6a95145'
        );

        $repository->getPermissionResolver()->setCurrentUserReference(
            $this->createUserWithPolicies(
                'trash_test_user',
                [
                    ['module' => 'content', 'function' => 'remove'],
                ]
            )
        );
        $trashService->trash($mediaLocation);
    }

    /**
     * Test for the recover() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::recover()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecover
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUser
     */
    public function testRecoverThrowsUnauthorizedException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user
        $trashItem = $this->createTrashItem();

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with an "UnauthorizedException"
        $trashService->recover($trashItem);
        /* END: Use Case */
    }

    /**
     * Test for the recover() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::recover($trashItem, $newParentLocation)
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testRecover
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUser
     */
    public function testRecoverThrowsUnauthorizedExceptionWithNewParentLocationParameter()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();

        $homeLocationId = $this->generateId('location', 2);
        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user
        // $homeLocationId is the ID of the "Home" location in an eZ Publish
        // demo installation

        $trashItem = $this->createTrashItem();

        // Get the new parent location
        $newParentLocation = $locationService->loadLocation($homeLocationId);

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with an "UnauthorizedException"
        $trashService->recover($trashItem, $newParentLocation);
        /* END: Use Case */
    }

    /**
     * Test for the emptyTrash() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::emptyTrash()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testEmptyTrash
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUser
     */
    public function testEmptyTrashThrowsUnauthorizedException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user
        $this->createTrashItem();

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with an "UnauthorizedException"
        $trashService->emptyTrash();
        /* END: Use Case */
    }

    /**
     * Test for the deleteTrashItem() method.
     *
     * @see \eZ\Publish\API\Repository\TrashService::deleteTrashItem()
     * @depends eZ\Publish\API\Repository\Tests\TrashServiceTest::testDeleteTrashItem
     * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadUser
     */
    public function testDeleteTrashItemThrowsUnauthorizedException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $trashService = $repository->getTrashService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user
        $trashItem = $this->createTrashItem();

        // Load user service
        $userService = $repository->getUserService();

        // Set "Anonymous" as current user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with an "UnauthorizedException"
        $trashService->deleteTrashItem($trashItem);
        /* END: Use Case */
    }

    public function testTrashRequiresPremissionsToRemoveAllSubitems()
    {
        $this->createRoleWithPolicies('Publisher', [
            ['module' => 'content', 'function' => 'read'],
            ['module' => 'content', 'function' => 'create'],
            ['module' => 'content', 'function' => 'publish'],
            ['module' => 'state', 'function' => 'assign'],
            ['module' => 'content', 'function' => 'remove', 'limitations' => [
                new ObjectStateLimitation(['limitationValues' => [
                    $this->generateId('objectstate', 2),
                ]]),
            ]],
        ]);
        $publisherUser = $this->createCustomUserWithLogin(
            'publisher',
            'publisher@example.com',
            'Publishers',
            'Publisher'
        );
        /** @var Repository $repository */
        $repository = $this->getRepository();
        $repository->getPermissionResolver()->setCurrentUserReference($publisherUser);
        $trashService = $repository->getTrashService();
        $locationService = $repository->getLocationService();
        $objectStateService = $repository->getObjectStateService();
        $parentContent = $this->createFolder(['eng-US' => 'Parent Folder'], 2);
        $objectStateService->setContentState(
            $parentContent->contentInfo,
            $objectStateService->loadObjectStateGroup(2),
            $objectStateService->loadObjectState(2)
        );
        $parentLocation = $locationService->loadLocations($parentContent->contentInfo)[0];
        $childContent = $this->createFolder(['eng-US' => 'Child Folder'], $parentLocation->id);

        $this->refreshSearch($repository);
        $this->expectException(\eZ\Publish\Core\Base\Exceptions\UnauthorizedException::class);
        $trashService->trash($parentLocation);
    }
}
