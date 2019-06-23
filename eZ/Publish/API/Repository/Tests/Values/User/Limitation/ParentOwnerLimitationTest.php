<?php

/**
 * File containing the ParentOwnerLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation
 * @group integration
 * @group limitation
 */
class ParentOwnerLimitationTest extends BaseLimitationTest
{
    /**
     * Tests the ParentOwnerLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation
     */
    public function testParentOwnerLimitationAllow()
    {
        $repository = $this->getRepository();

        $parentContentId = $this->generateId('content', 58);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentOwnerLimitation(
                ['limitationValues' => [1]]
            )
        );

        $role = $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $contentService = $repository->getContentService();

        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->ownerId = $user->id;

        $contentService->updateContentMetadata(
            $contentService->loadContentInfo($parentContentId),
            $metadataUpdate
        );

        $repository->setCurrentUser($user);

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue('title')->text
        );
    }

    /**
     * Tests the ParentOwnerLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testParentOwnerLimitationForbid()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentOwnerLimitation(
                ['limitationValues' => [1]]
            )
        );

        $role = $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $this->createWikiPageDraft();
        /* END: Use Case */
    }
}
