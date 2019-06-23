<?php

/**
 * File containing the ObjectStateLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation
 * @group integration
 * @group limitation
 */
class ObjectStateLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a ObjectStateLimitation.
     *
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation
     *
     * @throws \ErrorException
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testObjectStateLimitationAllow()
    {
        $repository = $this->getRepository();
        $notLockedState = $this->generateId('objectstate', 2);

        $contentService = $repository->getContentService();
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

        // Only allow deletion of content with default state
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ObjectStateLimitation(
                [
                    'limitationValues' => [
                        $notLockedState,
                    ],
                ]
            )
        );
        $roleService->updatePolicy($removePolicy, $policyUpdate);

        // Allow user to create everything
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $draft = $this->createWikiPageDraft();

        $contentService->deleteContent($draft->contentInfo);
        /* END: Use Case */

        $contentService->loadContent($draft->id);
    }

    /**
     * Tests a ObjectStateLimitation.
     *
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation
     *
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testObjectStateLimitationForbid()
    {
        $repository = $this->getRepository();
        $lockedState = $this->generateId('objectstate', 1);

        $contentService = $repository->getContentService();
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

        // Only allow deletion of content with default state
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ObjectStateLimitation(
                [
                    'limitationValues' => [
                        $lockedState,
                    ],
                ]
            )
        );
        $roleService->updatePolicy($removePolicy, $policyUpdate);

        // Allow user to create everything
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $draft = $this->createWikiPageDraft();

        $contentService->deleteContent($draft->contentInfo);
        /* END: Use Case */
    }

    /**
     * Tests an ObjectStateLimitation.
     *
     * Checks if the action is correctly forbidden when using ObjectStateLimitation
     * with limitation values from two different StateGroups.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation
     *
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @expectedExceptionMessage 'remove' 'content'
     */
    public function testObjectStateLimitationForbidVariant()
    {
        $repository = $this->getRepository();
        $objectStateGroup = $this->createObjectStateGroup();
        $objectState = $this->createObjectState($objectStateGroup);

        $lockedState = $this->generateId('objectstate', 1);
        $defaultStateFromAnotherGroup = $this->generateId('objectstate', $objectState->id);

        $contentService = $repository->getContentService();
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $removePolicy = null;
        foreach ($role->getPolicies() as $policy) {
            if ('content' !== $policy->module || 'remove' !== $policy->function) {
                continue;
            }
            $removePolicy = $policy;
            break;
        }

        $this->assertNotNull($removePolicy);

        // Only allow deletion of content with locked state and the default state from another State Group
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ObjectStateLimitation(
                [
                    'limitationValues' => [
                        $lockedState,
                        $defaultStateFromAnotherGroup,
                    ],
                ]
            )
        );
        $roleService->updatePolicy($removePolicy, $policyUpdate);

        // Allow user to create everything
        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $repository->getPermissionResolver()->setCurrentUserReference($user);

        $draft = $this->createWikiPageDraft();

        $contentService->deleteContent($draft->contentInfo);
        /* END: Use Case */
    }

    /**
     * Create new State Group.
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup
     */
    private function createObjectStateGroup()
    {
        $objectStateService = $this->getRepository()->getObjectStateService();

        $objectStateGroupCreateStruct = $objectStateService->newObjectStateGroupCreateStruct('second_group');
        $objectStateGroupCreateStruct->defaultLanguageCode = 'eng-US';
        $objectStateGroupCreateStruct->names = ['eng-US' => 'Second Group'];

        return $objectStateService->createObjectStateGroup($objectStateGroupCreateStruct);
    }

    /**
     * Create new State and assign it to the $objectStateGroup.
     *
     * @param \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup $objectStateGroup
     *
     * @return \eZ\Publish\API\Repository\Values\ObjectState\ObjectState
     */
    private function createObjectState(ObjectStateGroup $objectStateGroup)
    {
        $objectStateService = $this->getRepository()->getObjectStateService();

        $objectStateCreateStruct = $objectStateService->newObjectStateCreateStruct('default_state');
        $objectStateCreateStruct->defaultLanguageCode = 'eng-US';
        $objectStateCreateStruct->names = ['eng-US' => 'Default state'];

        return $objectStateService->createObjectState($objectStateGroup, $objectStateCreateStruct);
    }

    /**
     * Tests an ObjectStateLimitation.
     *
     * Checks if the search results are correctly filtered when using ObjectStateLimitation
     * with limitation values from two different StateGroups.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation
     */
    public function testObjectStateLimitationSearch()
    {
        $repository = $this->getRepository();
        $objectStateGroup = $this->createObjectStateGroup();
        $objectState = $this->createObjectState($objectStateGroup);

        $lockedState = $this->generateId('objectstate', 1);
        $defaultStateFromAnotherGroup = $this->generateId('objectstate', $objectState->id);

        $roleService = $repository->getRoleService();
        $roleName = 'role_with_object_state_limitation';
        $roleCreateStruct = $roleService->newRoleCreateStruct($roleName);
        $this->addPolicyToNewRole($roleCreateStruct, 'content', 'read', [
            new ObjectStateLimitation([
                'limitationValues' => [$lockedState, $defaultStateFromAnotherGroup],
            ]),
        ]);
        $roleService->publishRoleDraft(
            $roleService->createRole($roleCreateStruct)
        );

        $permissionResolver = $repository->getPermissionResolver();
        $user = $this->createCustomUserVersion1('Test group', $roleName);
        $adminUser = $permissionResolver->getCurrentUserReference();

        $wikiPage = $this->createWikiPage();

        $permissionResolver->setCurrentUserReference($user);

        $query = new Query();
        $query->filter = new Criterion\MatchAll();
        $query->limit = 50;

        $this->refreshSearch($repository);
        $searchResultsBefore = $repository->getSearchService()->findContent($query);

        $permissionResolver->setCurrentUserReference($adminUser);

        //change the Object State to the one that doesn't match the Limitation
        $stateService = $repository->getObjectStateService();
        $stateService->setContentState(
            $wikiPage->contentInfo,
            $stateService->loadObjectStateGroup(2),
            $stateService->loadObjectState(2)
        );

        $permissionResolver->setCurrentUserReference($user);

        $this->refreshSearch($repository);
        $searchResultsAfter = $repository->getSearchService()->findContent($query);

        $this->assertEquals($searchResultsBefore->totalCount - 1, $searchResultsAfter->totalCount);
    }

    /**
     * Add policy to a new role.
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $roleCreateStruct
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     */
    private function addPolicyToNewRole(RoleCreateStruct $roleCreateStruct, $module, $function, array $limitations)
    {
        $roleService = $this->getRepository()->getRoleService();
        $policyCreateStruct = $roleService->newPolicyCreateStruct($module, $function);
        foreach ($limitations as $limitation) {
            $policyCreateStruct->addLimitation($limitation);
        }
        $roleCreateStruct->addPolicy($policyCreateStruct);
    }
}
