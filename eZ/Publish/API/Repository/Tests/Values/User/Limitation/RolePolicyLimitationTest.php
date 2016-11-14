<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroup;

class RolePolicyLimitationTest extends BaseLimitationTest
{
    /**
     * Data provider for {@see testRolePoliciesWithOverlappingLimitations}.
     */
    public function providerForTestRolePoliciesWithOverlappingLimitations()
    {
        // get actual locations count for the given subtree when user is (by default) an admin
        $actualSubtreeLocationsCount = $this->getSubtreeLocationsCount('/1/2/');
        $this->assertGreaterThan(0, $actualSubtreeLocationsCount);

        return [
            [$actualSubtreeLocationsCount, 'content', '*'],
            [$actualSubtreeLocationsCount, 'content', 'read'],
            [$actualSubtreeLocationsCount, '*', '*'],
            // different module / all functions should not overlap other policies
            [0, 'user', '*'],
        ];
    }

    /**
     * Test if role with wider policy is not overlapped by limitation (uncovered in EZP-26476).
     *
     * @dataProvider providerForTestRolePoliciesWithOverlappingLimitations
     * @param int $expectedSubtreeLocationsCount
     * @param string $widePolicyModule
     * @param string $widePolicyFunction
     */
    public function testRolePoliciesWithOverlappingLimitations(
        $expectedSubtreeLocationsCount,
        $widePolicyModule,
        $widePolicyFunction
    ) {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();

        $subtreePathString = '/1/2/';

        // EZP-26476 use case:

        // create new role with overlapping limitation
        $roleName = 'role_with_overlapping_policies';
        $roleCreateStruct = $roleService->newRoleCreateStruct($roleName);

        $this->addPolicyToNewRole($roleCreateStruct, $widePolicyModule, $widePolicyFunction, []);
        $this->addPolicyToNewRole($roleCreateStruct, 'user', 'login', []);
        $this->addPolicyToNewRole($roleCreateStruct, 'content', 'read', [
            new ContentTypeLimitation([
                'limitationValues' => [4, 3],
            ]),
            new SectionLimitation([
                'limitationValues' => [2],
            ]),
        ]);

        $roleService->publishRoleDraft(
            $roleService->createRole($roleCreateStruct)
        );

        $role = $roleService->loadRoleByIdentifier($roleName);

        // create group and assign new role to that group, limited by subtree
        $userGroup = $this->createGroup('Test group', 'eng-US', 4);
        $roleService->assignRoleToUserGroup($role, $userGroup, new SubtreeLimitation([
            'limitationValues' => [$subtreePathString],
        ]));

        // create user assigned to the just created group
        $user = $this->createUserInGroup($userGroup);
        $repository->setCurrentUser($user);

        $this->refreshSearch($repository);

        // check if searching by subtree returns the same result as for an admin
        $this->assertEquals($expectedSubtreeLocationsCount, $this->getSubtreeLocationsCount($subtreePathString));

        // check if searching by subtree which is not a part of role assignment limitation does not return results
        $this->assertEquals(0, $this->getSubtreeLocationsCount('/1/5/'));
    }

    /**
     * Perform search by the Subtree Criterion for the given subtree path and return results count.
     *
     * @param $subtreePathString
     * @return int|null
     */
    protected function getSubtreeLocationsCount($subtreePathString)
    {
        $criterion = new Criterion\Subtree($subtreePathString);
        $query = new LocationQuery(['filter' => $criterion]);

        $result = $this->getRepository()->getSearchService()->findLocations($query);

        return $result->totalCount;
    }

    /**
     * Create test User in the given User Group.
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $group
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function createUserInGroup(UserGroup $group)
    {
        $userService = $this->getRepository()->getUserService();

        // Instantiate a create struct with mandatory properties
        $userCreateStruct = $userService->newUserCreateStruct(
            'user',
            'user@example.com',
            'secret',
            'eng-US'
        );
        $userCreateStruct->enabled = true;

        // Set some fields required by the user ContentType
        $userCreateStruct->setField('first_name', 'Example');
        $userCreateStruct->setField('last_name', 'User');

        // Create a new user instance.
        $user = $userService->createUser($userCreateStruct, [$group]);

        return $user;
    }

    /**
     * Add policy to a new role.
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     */
    protected function addPolicyToNewRole(RoleCreateStruct $roleCreateStruct, $module, $function, array $limitations)
    {
        $roleService = $this->getRepository()->getRoleService();
        $policyCreateStruct = $roleService->newPolicyCreateStruct($module, $function);
        foreach ($limitations as $limitation) {
            $policyCreateStruct->addLimitation($limitation);
        }
        $roleCreateStruct->addPolicy($policyCreateStruct);
    }

    /**
     * Create User Group.
     *
     * @param string $groupName
     * @param string $mainLanguageCode
     * @param int $parentGroupId
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    protected function createGroup($groupName, $mainLanguageCode, $parentGroupId)
    {
        $userService = $this->getRepository()->getUserService();

        $userGroupCreateStruct = $userService->newUserGroupCreateStruct($mainLanguageCode);
        $usersGroup = $userService->loadUserGroup($parentGroupId);
        $userGroupCreateStruct->setField('name', $groupName);

        return $userService->createUserGroup($userGroupCreateStruct, $usersGroup);
    }
}
