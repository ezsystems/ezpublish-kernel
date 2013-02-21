<?php
/**
 * File containing the ContentTypeLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

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
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @throws \ErrorException
     */
    public function testContentTypeLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId( 'contentType', 22 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'edit' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:edit policy found.' );
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $roleService->updatePolicy( $editPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $roleService->loadRole( $role->id ), $user );

        $content = $this->createWikiPage();

        $repository->setCurrentUser( $user );

        $updateDraft = $contentService->createContentDraft( $content->contentInfo );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'title', 'Your wiki page' );

        $updateContent = $contentService->updateContent(
            $updateDraft->versionInfo,
            $contentUpdate
        );
        /* END: Use Case */

        $this->assertEquals(
            'Your wiki page',
            $updateContent->getFieldValue( 'title' )->text
        );
    }

    /**
     * Test for the ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testContentTypeLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId( 'contentType', 33 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'edit' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:edit policy found.' );
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $roleService->updatePolicy( $editPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $roleService->loadRole( $role->id ), $user );

        $content = $this->createWikiPage();

        $repository->setCurrentUser( $user );

        // This call fails with an UnauthorizedException
        $contentService->createContentDraft( $content->contentInfo );
        /* END: Use Case */
    }

    /**
     * Test for the ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testContentTypeLimitationForbidVariant()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        $contentTypeId = $this->generateId( 'contentType', 33 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'edit' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:edit policy found.' );
        }

        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $roleService->updatePolicy( $editPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $roleService->loadRole( $role->id ), $user );

        $content = $this->createWikiPage();

        $updateDraft = $contentService->createContentDraft( $content->contentInfo );

        $repository->setCurrentUser( $user );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'title', 'Your wiki page' );

        // This call fails with an UnauthorizedException
        $contentService->updateContent(
            $updateDraft->versionInfo,
            $contentUpdate
        );
        /* END: Use Case */
    }
}
