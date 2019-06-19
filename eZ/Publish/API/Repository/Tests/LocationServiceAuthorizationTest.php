<?php

/**
 * File containing the LocationServiceAuthorizationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;

/**
 * Test case for operations in the LocationService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\LocationService
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testCreateUser
 * @group integration
 * @group authorization
 */
class LocationServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testCreateLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $user = $this->createUserVersion1();

        // ContentInfo for "Editors" user group
        $contentInfo = $contentService->loadContentInfo($editorsGroupId);

        // Set current user to newly created user
        $repository->setCurrentUser($user);

        $locationCreate = $locationService->newLocationCreateStruct(1);
        $locationCreate->priority = 23;
        $locationCreate->hidden = true;
        $locationCreate->remoteId = 'sindelfingen';
        $locationCreate->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreate->sortOrder = Location::SORT_ORDER_DESC;

        // This call will fail with an "UnauthorizedException"
        $locationService->createLocation(
            $contentInfo,
            $locationCreate
        );
        /* END: Use Case */
    }

    /**
     * Test for the createLocation() method. Tests a case when user doesn't have content/manage_locations policy for the new location ID.
     *
     * @see \eZ\Publish\API\Repository\LocationService::createLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCreateLocation
     */
    public function testCreateLocationThrowsUnauthorizedExceptionDueToLackOfContentManageLocationsPolicy()
    {
        $repository = $this->getRepository();

        $mediaDirectoryLocationId = $this->generateId('location', '43');

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();
        // Location for "Media" directory
        $contentLocation = $locationService->loadLocation($mediaDirectoryLocationId);

        // Create the new "Dummy" user group
        $userService = $repository->getUserService();
        $userGroupCreateStruct = $userService->newUserGroupCreateStruct('eng-GB');
        $userGroupCreateStruct->setField('name', 'Dummy');
        $dummyUserGroup = $userService->createUserGroup($userGroupCreateStruct, $userService->loadUserGroup(4));

        // Create the new "Dummy" role with content/* policy limited by Subtree to "Media" folder
        $roleService = $repository->getRoleService();
        $role = $this->createRoleWithPolicies('Dummy', [
            [
                'module' => 'content',
                'function' => 'read',
                'limitations' => [],
            ],
            [
                'module' => 'content',
                'function' => 'manage_locations',
                'limitations' => [new SubtreeLimitation(['limitationValues' => [$contentLocation->pathString]])],
            ],
        ]);

        $user = $this->createUser('johndoe', 'John', 'Doe', $dummyUserGroup);
        $roleService->assignRoleToUser($role, $user);
        // Set current user to newly created user
        $repository->setCurrentUser($user);

        $locationCreateStruct = $locationService->newLocationCreateStruct('2');
        $locationCreateStruct->priority = 12;
        $locationCreateStruct->hidden = false;
        $locationCreateStruct->sortField = Location::SORT_FIELD_NODE_ID;
        $locationCreateStruct->sortOrder = Location::SORT_ORDER_DESC;

        // This call will fail with an "UnauthorizedException"
        $locationService->createLocation(
            $contentLocation->contentInfo,
            $locationCreateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocation
     */
    public function testLoadLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $locationService = $repository->getLocationService();

        $user = $this->createUserVersion1();

        // Set current user to newly created user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->loadLocation($editorsGroupId);
        /* END: Use Case */
    }

    /**
     * Test for the loadLocationList() method.
     *
     * @covers \eZ\Publish\API\Repository\LocationService::loadLocationList
     */
    public function testLoadLocationListFiltersUnauthorizedLocations(): void
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        // Set current user to newly created user (with no rights)
        $repository->getPermissionResolver()->setCurrentUserReference(
            $this->createUserVersion1()
        );

        $locations = $locationService->loadLocationList([13]);

        self::assertInternalType('iterable', $locations);
        self::assertCount(0, $locations);
    }

    /**
     * Test for the loadLocationByRemoteId() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocationByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocationByRemoteId
     */
    public function testLoadLocationByRemoteIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // remoteId of the "Editors" location in an eZ Publish demo installation
        $editorsRemoteId = 'f7dda2854fc68f7c8455d9cb14bd04a9';

        $locationService = $repository->getLocationService();

        $user = $this->createUserVersion1();

        // Set current user to newly created user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->loadLocationByRemoteId($editorsRemoteId);
        /* END: Use Case */
    }

    /**
     * Test for the loadLocations() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::loadLocations()
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testLoadLocations
     */
    public function testLoadLocationsNoAccess()
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $editorsGroupId = $this->generateId('group', 13);
        $editorGroupContentInfo = $repository->getContentService()->loadContentInfo($editorsGroupId);

        // this should return one location for admin
        $locations = $locationService->loadLocations($editorGroupContentInfo);
        $this->assertCount(1, $locations);
        $this->assertInstanceOf(Location::class, $locations[0]);

        $user = $this->createUserVersion1();

        // Set current user to newly created user
        $repository->getPermissionResolver()->setCurrentUserReference($user);

        // This should return empty array given current user does not have read access
        $locations = $locationService->loadLocations($editorGroupContentInfo);
        $this->assertEmpty($locations);
    }

    /**
     * Test for the updateLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::updateLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testUpdateLocation
     */
    public function testUpdateLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $originalLocation = $locationService->loadLocation($editorsGroupId);

        $locationUpdateStruct = $locationService->newLocationUpdateStruct();
        $locationUpdateStruct->priority = 3;
        $locationUpdateStruct->remoteId = 'c7adcbf1e96bc29bca28c2d809d0c7ef69272651';
        $locationUpdateStruct->sortField = Location::SORT_FIELD_PRIORITY;
        $locationUpdateStruct->sortOrder = Location::SORT_ORDER_DESC;

        // Set current user to newly created user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->updateLocation(
            $originalLocation,
            $locationUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the swapLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::swapLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testSwapLocation
     */
    public function testSwapLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        // $mediaLocationId is the ID of the "Media" Location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" Location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        $mediaLocation = $locationService->loadLocation($mediaLocationId);
        $demoDesignLocation = $locationService->loadLocation($demoDesignLocationId);

        // Swaps the content referred to by the locations
        $locationService->swapLocation($mediaLocation, $demoDesignLocation);

        $user = $this->createMediaUserVersion1();

        // Set media editor as current user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->swapLocation($mediaLocation, $demoDesignLocation);
        /* END: Use Case */
    }

    /**
     * Test for the hideLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::hideLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testHideLocation
     */
    public function testHideLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation($editorsGroupId);

        // Set current user to newly created user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->hideLocation($visibleLocation);
        /* END: Use Case */
    }

    /**
     * Test for the unhideLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::unhideLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testUnhideLocation
     */
    public function testUnhideLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $visibleLocation = $locationService->loadLocation($editorsGroupId);

        // Hide location
        $hiddenLocation = $locationService->hideLocation($visibleLocation);

        // Set current user to newly created user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->unhideLocation($hiddenLocation);
        /* END: Use Case */
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testDeleteLocation
     */
    public function testDeleteLocationThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $editorsGroupId = $this->generateId('group', 13);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $locationService = $repository->getLocationService();

        $location = $locationService->loadLocation($editorsGroupId);

        // Set current user to newly created user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->deleteLocation($location);
        /* END: Use Case */
    }

    /**
     * Test for the deleteLocation() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::deleteLocation()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @expectedExceptionMessage User does not have access to 'remove' 'content'
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testDeleteLocation
     */
    public function testDeleteLocationWithSubtreeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId('location', 43);
        $administratorUserId = $this->generateId('user', 14);

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $removePolicy = null;
        foreach ($role->getPolicies() as $policy) {
            if ('content' != $policy->module || 'remove' != $policy->function) {
                continue;
            }
            $removePolicy = $policy;
            break;
        }

        if (null === $removePolicy) {
            throw new \ErrorException('No content:remove policy found.');
        }

        // Update content/remove policy to only allow removal of the user's own content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new OwnerLimitation(
                ['limitationValues' => [1]]
            )
        );
        $roleService->updatePolicy($removePolicy, $policyUpdate);

        // Set current user to newly created user
        $repository->setCurrentUser($user);

        $locationService = $repository->getLocationService();
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $userService = $repository->getUserService();

        // Create and publish Content with Location under $parentLocationId
        $contentType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $contentCreateStruct = $contentService->newContentCreateStruct($contentType, 'eng-US');
        $contentCreateStruct->setField('name', 'My awesome possibly deletable folder');
        $contentCreateStruct->alwaysAvailable = true;

        $locationCreateStruct = $locationService->newLocationCreateStruct($parentLocationId);

        $contentDraft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $content = $contentService->publishVersion($contentDraft->versionInfo);

        // New user will be able to delete this Location at this point
        $firstLocation = $locationService->loadLocation($content->contentInfo->mainLocationId);

        // Set current user to administrator user
        $administratorUser = $userService->loadUser($administratorUserId);
        $repository->setCurrentUser($administratorUser);

        // Under newly created Location create Content with administrator user
        // After this created user will not be able to delete $firstLocation
        $locationCreateStruct = $locationService->newLocationCreateStruct($firstLocation->id);
        $contentDraft = $contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
        $content = $contentService->publishVersion($contentDraft->versionInfo);
        $secondLocation = $locationService->loadLocation($content->contentInfo->mainLocationId);

        // Set current user to newly created user again, and try to delete $firstLocation
        $repository->setCurrentUser($user);

        $this->refreshSearch($repository);

        // This call will fail with an "UnauthorizedException" because user does not have
        // permission to delete $secondLocation which is in the subtree of the $firstLocation
        $locationService->deleteLocation($firstLocation);
        /* END: Use Case */
    }

    /**
     * Test for the copySubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::copySubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testCopySubtree
     */
    public function testCopySubtreeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // $mediaLocationId is the ID of the "Media" Location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" Location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to copy
        $locationToCopy = $locationService->loadLocation($mediaLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        // Set media editor as current user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->copySubtree(
            $locationToCopy,
            $newParentLocation
        );
        /* END: Use Case */
    }

    /**
     * Test for the moveSubtree() method.
     *
     * @see \eZ\Publish\API\Repository\LocationService::moveSubtree()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\LocationServiceTest::testMoveSubtree
     */
    public function testMoveSubtreeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $mediaLocationId = $this->generateId('location', 43);
        $demoDesignLocationId = $this->generateId('location', 56);
        /* BEGIN: Use Case */
        $user = $this->createMediaUserVersion1();

        // $mediaLocationId is the ID of the "Media" page location in
        // an eZ Publish demo installation

        // $demoDesignLocationId is the ID of the "Demo Design" page location in an eZ
        // Publish demo installation

        // Load the location service
        $locationService = $repository->getLocationService();

        // Load location to move
        $locationToMove = $locationService->loadLocation($mediaLocationId);

        // Load new parent location
        $newParentLocation = $locationService->loadLocation($demoDesignLocationId);

        // Set media editor as current user
        $repository->setCurrentUser($user);

        // This call will fail with an "UnauthorizedException"
        $locationService->moveSubtree(
            $locationToMove,
            $newParentLocation
        );
        /* END: Use Case */
    }
}
