<?php

/**
 * File containing the ParentDepthLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
 * @group integration
 * @group limitation
 */
class ParentDepthLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a combination of ParentDepthLimitation and ContentTypeLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testParentDepthLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentTypeId = $this->generateId('contentType', 22);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentDepthLimitation(
                ['limitationValues' => [3]]
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                ['limitationValues' => [$contentTypeId]]
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
     * Tests a combination of ParentDepthLimitation and ContentTypeLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
     */
    public function testParentDepthLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentTypeId = $this->generateId('contentType', 22);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentDepthLimitation(
                ['limitationValues' => [1, 2, 3, 4]]
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                ['limitationValues' => [$contentTypeId]]
            )
        );

        $role = $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $this->createWikiPageDraft();
        /* END: Use Case */
    }

    /**
     * Tests a combination of ParentDepthLimitation and ContentTypeLimitation.
     *
     * @depends testParentDepthLimitationAllow
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
     */
    public function testParentDepthLimitationAllowPublish()
    {
        $repository = $this->getRepository();

        $contentTypeId = $this->generateId('contentType', 22);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $policyCreate = $roleService->newPolicyCreateStruct('content', 'create');
        $policyCreate->addLimitation(
            new ParentDepthLimitation(
                ['limitationValues' => [1, 2, 3, 4]]
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                ['limitationValues' => [$contentTypeId]]
            )
        );

        $role = $roleService->addPolicy($role, $policyCreate);

        $roleService->assignRoleToUser($role, $user);

        $repository->setCurrentUser($user);

        $draft = $this->createWikiPageDraft();

        $contentService = $repository->getContentService();

        $content = $contentService->publishVersion($draft->versionInfo);
        /* END: Use Case */
    }
}
