<?php

/**
 * File containing the ParentContentTypeLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
 * @group integration
 * @group limitation
 */
class ParentContentTypeLimitationTest extends BaseLimitationTest
{
    /**
     * Test for ParentContentTypeLimitation and ContentTypeLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
     */
    public function testParentContentTypeLimitationAllow()
    {
        $repository = $this->getRepository();
        $permissionResolver = $repository->getPermissionResolver();

        $parentContentTypeId = $this->generateId('contentType', 20);
        $contentTypeId = $this->generateId('contentType', 22);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();
        $contentService = $repository->getContentService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentContentTypeLimitation(
                ['limitationValues' => [$parentContentTypeId]]
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                ['limitationValues' => [$contentTypeId]]
            )
        );

        $roleDraft = $roleService->createRoleDraft($role);
        $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $policyCreate
        );
        $roleService->publishRoleDraft($roleDraft);

        $roleService->assignRoleToUser($role, $user);

        $permissionResolver->setCurrentUserReference($user);

        $draft = $this->createWikiPageDraft();
        $content = $contentService->publishVersion($draft->versionInfo);
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $content->getFieldValue('title')->text
        );
    }

    /**
     * Test for ParentContentTypeLimitation and ContentTypeLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
     */
    public function testParentContentTypeLimitationForbid()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $permissionResolver = $repository->getPermissionResolver();

        $parentContentTypeId = $this->generateId('contentType', 20);
        $contentTypeId = $this->generateId('contentType', 33);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentContentTypeLimitation(
                ['limitationValues' => [$parentContentTypeId]]
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                ['limitationValues' => [$contentTypeId]]
            )
        );

        $roleDraft = $roleService->createRoleDraft($role);
        $roleService->addPolicyByRoleDraft(
            $roleDraft,
            $policyCreate
        );
        
        $roleService->publishRoleDraft($roleDraft);

        $permissionResolver->setCurrentUserReference($user);

        $this->createWikiPageDraft();
        /* END: Use Case */
    }
}
