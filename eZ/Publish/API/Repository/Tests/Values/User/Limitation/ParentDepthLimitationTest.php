<?php
/**
 * File containing the ParentDepthLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

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
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
     */
    public function testParentDepthLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentTypeId = $this->generateId( 'contentType', 22 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentDepthLimitation(
                array( 'limitationValues' => array( 2 ) )
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
     * Tests a combination of ParentDepthLimitation and ContentTypeLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\ParentDepthLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testParentDepthLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentTypeId = $this->generateId( 'contentType', 22 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentDepthLimitation(
                array( 'limitationValues' => array( 1, 3, 4 ) )
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
