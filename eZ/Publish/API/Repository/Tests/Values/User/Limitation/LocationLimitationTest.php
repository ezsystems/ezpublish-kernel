<?php

/**
 * File containing the LocationLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation
 * @group integration
 * @group limitation
 */
class LocationLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a LocationLimitation.
     *
     * @see eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation
     */
    public function testLocationLimitationAllow()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId('location', 60);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new LocationLimitation(
                ['limitationValues' => [$parentLocationId]]
            )
        );

        $role = $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue('title')->text
        );
    }

    /**
     * Tests a LocationLimitation.
     *
     * @see eZ\Publish\API\Repository\Values\User\Limitation\LocationLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLocationLimitationForbid()
    {
        $repository = $this->getRepository();

        $parentLocationId = $this->generateId('location', 61);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new LocationLimitation(
                ['limitationValues' => [$parentLocationId]]
            )
        );

        $role = $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $this->createWikiPageDraft();
        /* END: Use Case */
    }
}
