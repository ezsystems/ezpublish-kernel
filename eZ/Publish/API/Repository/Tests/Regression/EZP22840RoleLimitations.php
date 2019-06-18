<?php

/**
 * File part of eZ Publish API test suite.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Regression;

use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\User\Limitation\NewObjectStateLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;

/**
 * Issue EZP-22840.
 */
class EZP22840RoleLimitations extends BaseTest
{
    /**
     * Test Subtree Role Assignment Limitation against state/assign.
     */
    public function testSubtreeRoleAssignLimitation()
    {
        $repository = $this->getRepository();
        $roleService = $repository->getRoleService();
        $notLockedState = $this->generateId('objectstate', 2);
        $contentId = $this->generateId('content', 57);
        $objectStateService = $repository->getObjectStateService();

        // Get user assigned to editor role
        $user = $this->createUserVersion1();

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

        $roleService->addPolicy(
            $roleService->loadRoleByIdentifier('Editor'),
            $policyCreate
        );

        // set current user and get objects needed for the test
        $repository->setCurrentUser($user);
        $objectState = $objectStateService->loadObjectState($notLockedState);
        $contentInfo = $repository->getContentService()->loadContentInfo($contentId);

        // try to assign object state to root object
        $objectStateService->setContentState($contentInfo, $objectState->getObjectStateGroup(), $objectState);
    }

    /**
     * Test Section Role Assignment Limitation against user/login.
     */
    public function testSectionRoleAssignLimitation()
    {
        $repository = $this->getRepository();

        // Get user assigned to editor role with section limitation
        $user = $this->createCustomUserVersion1(
            'Section Editor',
            'Editor',
            new SectionLimitation(['limitationValues' => ['2']])
        );

        // set as current user
        $repository->setCurrentUser($user);

        // try to login
        $this->assertTrue(
            $repository->canUser('user', 'login', new SiteAccess()),
            'Could not verify that user can login with section limitation'
        );
    }
}
