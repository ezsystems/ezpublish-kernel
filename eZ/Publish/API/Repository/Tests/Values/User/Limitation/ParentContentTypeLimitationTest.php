<?php
/**
 * File containing the ParentContentTypeLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

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
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
     */
    public function testParentContentTypeLimitationAllow()
    {
        $repository = $this->getRepository();

        $parentContentTypeId = $this->generateId( 'contentType', 20 );
        $contentTypeId = $this->generateId( 'contentType', 22 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentContentTypeLimitation(
                array( 'limitationValues' => array( $parentContentTypeId ) )
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue( 'title' )->text
        );
    }

    /**
     * Test for ParentContentTypeLimitation and ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentContentTypeLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testParentContentTypeLimitationForbid()
    {
        $repository = $this->getRepository();

        $parentContentTypeId = $this->generateId( 'contentType', 20 );
        $contentTypeId = $this->generateId( 'contentType', 33 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentContentTypeLimitation(
                array( 'limitationValues' => array( $parentContentTypeId ) )
            )
        );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $contentTypeId ) )
            )
        );

        $role = $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $this->createWikiPageDraft();
        /* END: Use Case */
    }
}
