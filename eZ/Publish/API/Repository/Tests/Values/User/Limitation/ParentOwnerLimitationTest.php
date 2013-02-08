<?php
/**
 * File containing the ParentOwnerLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

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
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation
     */
    public function testParentOwnerLimitationAllow()
    {
        $repository = $this->getRepository();

        $parentContentId = $this->generateId( 'content', 58 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentOwnerLimitation(
                array( 'limitationValues' => array( 1 ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $contentService = $repository->getContentService();

        $metadataUpdate = $contentService->newContentMetadataUpdateStruct();
        $metadataUpdate->ownerId = $user->id;

        $contentService->updateContentMetadata(
            $contentService->loadContentInfo( $parentContentId ),
            $metadataUpdate
        );

        $repository->setCurrentUser( $user );

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue( 'title' )->text
        );
    }

    /**
     * Tests the ParentOwnerLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentOwnerLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testParentOwnerLimitationForbid()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentOwnerLimitation(
                array( 'limitationValues' => array( 1 ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $this->createWikiPageDraft();
        /* END: Use Case */
    }
}
