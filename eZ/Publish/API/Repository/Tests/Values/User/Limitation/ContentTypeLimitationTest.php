<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
 * @group integration
 * @group limitation
 */
class ContentTypeLimitationTest extends BaseLimitationTest
{
    /**
     * Test for the ContentTypeLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     *
     * @throws \ErrorException
     */
    public function testContentTypeLimitationAllow()
    {
        $repository = $this->getRepository();
        $permissionResolver = $repository->getPermissionResolver();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId('contentType', 22);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');
        $roleDraft = $roleService->createRoleDraft($role);
        $editPolicy = null;
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy */
        foreach ($roleDraft->getPolicies() as $policy) {
            if ('content' != $policy->module || 'edit' != $policy->function) {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if (null === $editPolicy) {
            throw new \ErrorException('No content:edit policy found.');
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                ['limitationValues' => [$contentTypeId]]
            )
        );

        $roleService->updatePolicyByRoleDraft(
            $roleDraft,
            $editPolicy,
            $policyUpdate
        );
        $roleService->publishRoleDraft($roleDraft);
        $roleService->assignRoleToUser($roleService->loadRole($role->id), $user);

        $content = $this->createWikiPage();

        $permissionResolver->setCurrentUserReference($user);

        $updateDraft = $contentService->createContentDraft($content->contentInfo);

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField('title', 'Your wiki page');

        $updateContent = $contentService->updateContent(
            $updateDraft->versionInfo,
            $contentUpdate
        );
        /* END: Use Case */

        $this->assertEquals(
            'Your wiki page',
            $updateContent->getFieldValue('title')->text
        );
    }

    /**
     * Test for the ContentTypeLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     *
     * @throws \ErrorException
     */
    public function testContentTypeLimitationForbid()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $permissionResolver = $repository->getPermissionResolver();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId('contentType', 33);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');
        $roleDraft = $roleService->createRoleDraft($role);
        $editPolicy = null;
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy */
        foreach ($roleDraft->getPolicies() as $policy) {
            if ('content' != $policy->module || 'edit' != $policy->function) {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if (null === $editPolicy) {
            throw new \ErrorException('No content:edit policy found.');
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                ['limitationValues' => [$contentTypeId]]
            )
        );

        $roleService->updatePolicyByRoleDraft(
            $roleDraft,
            $editPolicy,
            $policyUpdate
        );
        $roleService->publishRoleDraft($roleDraft);
        $roleService->assignRoleToUser($roleService->loadRole($role->id), $user);

        $content = $this->createWikiPage();

        $permissionResolver->setCurrentUserReference($user);

        // This call fails with an UnauthorizedException
        $contentService->createContentDraft($content->contentInfo);
        /* END: Use Case */
    }

    /**
     * Test for the ContentTypeLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     *
     * @throws \ErrorException
     */
    public function testContentTypeLimitationForbidVariant()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\UnauthorizedException::class);

        $repository = $this->getRepository();
        $permissionResolver = $repository->getPermissionResolver();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId('contentType', 33);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');
        $roleDraft = $roleService->createRoleDraft($role);
        // Search for the new policy instance
        $policy = null;
        /** @var \eZ\Publish\API\Repository\Values\User\PolicyDraft $policy */
        $editPolicy = null;
        foreach ($roleDraft->getPolicies() as $policy) {
            if ('content' != $policy->module || 'edit' != $policy->function) {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if (null === $editPolicy) {
            throw new \ErrorException('No content:edit policy found.');
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                ['limitationValues' => [$contentTypeId]]
            )
        );

        $roleService->updatePolicyByRoleDraft(
            $roleDraft,
            $editPolicy,
            $policyUpdate
        );
        $roleService->publishRoleDraft($roleDraft);
        $roleService->assignRoleToUser($roleService->loadRole($role->id), $user);

        $content = $this->createWikiPage();

        $updateDraft = $contentService->createContentDraft($content->contentInfo);

        $permissionResolver->setCurrentUserReference($user);

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField('title', 'Your wiki page');

        // This call fails with an UnauthorizedException
        $contentService->updateContent(
            $updateDraft->versionInfo,
            $contentUpdate
        );
        /* END: Use Case */
    }
}
