<?php
/**
 * File containing the OwnerLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

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
     * Test for the OwnerLimitation
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation
     * @throws \ErrorException
     */
    public function testOwnerLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $removePolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'remove' != $policy->function )
            {
                continue;
            }
            $removePolicy = $policy;
            break;
        }

        if ( null === $removePolicy )
        {
            throw new \ErrorException( 'No content:remove policy found.' );
        }

        // Only allow remove for the user's own content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new OwnerLimitation(
                array( 'limitationValues' => array( 1 ) )
            )
        );
        $roleService->updatePolicy( $removePolicy, $policyUpdate );

        $roleService->assignRoleToUser( $role, $user );

        $content = $this->createWikiPage();

        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->ownerId = $user->id;

        $contentService->updateContentMetadata(
            $content->contentInfo,
            $metadataUpdate
        );

        $repository->setCurrentUser( $user );

        $contentService->deleteContent(
            $contentService->loadContentInfo( $content->id )
        );
        /* END: Use Case */

        $this->setExpectedException(
            '\\eZ\\Publish\\API\\Repository\\Exceptions\\NotFoundException'
        );
        $contentService->loadContent( $content->id );
    }

    /**
     * Test for the OwnerLimitation
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\OwnerLimitation
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

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $removePolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'remove' != $policy->function )
            {
                continue;
            }
            $removePolicy = $policy;
            break;
        }

        if ( null === $removePolicy )
        {
            throw new \ErrorException( 'No content:remove policy found.' );
        }

        // Only allow remove for the user's own content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new OwnerLimitation(
                array( 'limitationValues' => array( 1 ) )
            )
        );
        $roleService->updatePolicy( $removePolicy, $policyUpdate );

        $roleService->assignRoleToUser( $role, $user );

        $content = $this->createWikiPage();

        $repository->setCurrentUser( $user );

        // This call fails with an UnauthorizedException, because the current
        // user is not the content owner
        $contentService->deleteContent(
            $contentService->loadContentInfo( $content->id )
        );
        /* END: Use Case */
    }
}
