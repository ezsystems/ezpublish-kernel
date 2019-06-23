<?php

/**
 * File containing the NewObjectStateLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation
 * @group integration
 * @group limitation
 */
class NewObjectStateLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a NewObjectStateLimitation.
     *
     * @see eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation
     *
     * @throws \ErrorException if a mandatory test fixture not exists.
     */
    public function testNewObjectStateLimitationAllow()
    {
        $repository = $this->getRepository();
        $notLockedState = $this->generateId('objectstate', 2);

        $objectStateService = $repository->getObjectStateService();
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();
        $draft = $this->createWikiPageDraft();

        $roleService = $repository->getRoleService();

        // Create and assign limited state:assign policy
        $policyCreate = $roleService->newPolicyCreateStruct('state', 'assign');
        $policyCreate->addLimitation(
            new NewObjectStateLimitation(
                [
                    'limitationValues' => [
                        $notLockedState,
                    ],
                ]
            )
        );

        $role = $roleService->addPolicy(
            $roleService->loadRoleByIdentifier('Editor'),
            $policyCreate
        );

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $objectState = $objectStateService->loadObjectState($notLockedState);

        $objectStateService->setContentState($draft->contentInfo, $objectState->getObjectStateGroup(), $objectState);
        /* END: Use Case */
    }

    /**
     * Tests a NewObjectStateLimitation.
     *
     * @see eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation
     *
     * @throws \ErrorException if a mandatory test fixture not exists.
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testNewObjectStateLimitationForbid()
    {
        $repository = $this->getRepository();
        $lockedState = $this->generateId('objectstate', 1);
        $notLockedState = $this->generateId('objectstate', 2);

        $objectStateService = $repository->getObjectStateService();
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();
        $draft = $this->createWikiPageDraft();

        $roleService = $repository->getRoleService();

        // Create and assign limited state:assign policy
        $policyCreate = $roleService->newPolicyCreateStruct('state', 'assign');
        $policyCreate->addLimitation(
            new NewObjectStateLimitation(
                [
                    'limitationValues' => [
                        $lockedState,
                    ],
                ]
            )
        );

        $role = $roleService->addPolicy(
            $roleService->loadRoleByIdentifier('Editor'),
            $policyCreate
        );

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $objectState = $objectStateService->loadObjectState($notLockedState);

        $objectStateService->setContentState($draft->contentInfo, $objectState->getObjectStateGroup(), $objectState);
        /* END: Use Case */
    }
}
