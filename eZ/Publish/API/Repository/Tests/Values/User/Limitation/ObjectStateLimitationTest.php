<?php

/**
 * File containing the ObjectStateLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;

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
                array(
                    'limitationValues' => array(
                        $notLockedState,
                    ),
                )
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

        $this->setExpectedException('\\eZ\\Publish\\API\\Repository\\Exceptions\\NotFoundException');
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
                array(
                    'limitationValues' => array(
                        $lockedState,
                    ),
                )
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
}
