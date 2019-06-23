<?php

/**
 * File containing the OwnerLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation
 * @group integration
 * @group limitation
 */
class OwnerLimitationTest extends BaseLimitationTest
{
    /**
     * Test for the OwnerLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation
     *
     * @throws \ErrorException
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testOwnerLimitationAllow()
    {
        $repository = $this->getRepository();

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

        // Only allow remove for the user's own content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new OwnerLimitation(
                ['limitationValues' => [1]]
            )
        );
        $roleService->updatePolicy($removePolicy, $policyUpdate);

        $roleService->assignRoleToUser($role, $user);

        $content = $this->createWikiPage();

        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->ownerId = $user->id;

        $contentService->updateContentMetadata(
            $content->contentInfo,
            $metadataUpdate
        );

        $repository->setCurrentUser($user);

        $contentService->deleteContent(
            $contentService->loadContentInfo($content->id)
        );
        /* END: Use Case */

        $contentService->loadContent($content->id);
    }

    /**
     * Test for the OwnerLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation
     *
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testOwnerLimitationForbid()
    {
        $repository = $this->getRepository();

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

        // Only allow remove for the user's own content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new OwnerLimitation(
                ['limitationValues' => [1]]
            )
        );
        $roleService->updatePolicy($removePolicy, $policyUpdate);

        $roleService->assignRoleToUser($role, $user);

        $content = $this->createWikiPage();

        $repository->setCurrentUser($user);

        // This call fails with an UnauthorizedException, because the current
        // user is not the content owner
        $contentService->deleteContent(
            $contentService->loadContentInfo($content->id)
        );
        /* END: Use Case */
    }
}
