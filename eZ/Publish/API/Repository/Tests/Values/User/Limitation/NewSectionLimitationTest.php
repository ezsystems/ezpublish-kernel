<?php
/**
 * File containing the NewSectionLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\NewSectionLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\NewSectionLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\NewSectionLimitation
 * @group integration
 * @group limitation
 */
class NewSectionLimitationTest extends BaseLimitationTest
{
    /**
     * Tests the NewSectionLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\NewSectionLimitation
     */
    public function testNewSectionLimitationAllow()
    {
        $repository = $this->getRepository();

        $sectionId = $this->generateId( 'section', 6 );
        $contentId = $this->generateId( 'content', 58 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct( 'section', 'assign' );
        $policyCreate->addLimitation(
            new NewSectionLimitation(
                array( 'limitationValues' => array( $sectionId ) )
            )
        );

        $role = $roleService->addPolicy(
            $roleService->loadRoleByIdentifier( 'Editor' ),
            $policyCreate
        );
        $role = $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct( 'section', 'view' )
        );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( $contentId );

        $sectionService = $repository->getSectionService();
        $sectionService->assignSection(
            $contentInfo,
            $sectionService->loadSection( $sectionId )
        );
        /* END: Use Case */

        $this->assertSame(
            $sectionId,
            $contentService->loadContentInfo( $contentId )->sectionId
        );
    }

    /**
     * Tests the NewSectionLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\NewSectionLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testNewSectionLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'content', 58 );
        $sectionId = $this->generateId( 'section', 6 );
        $otherSectionId = $this->generateId( 'section', 1 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct( 'section', 'assign' );
        $policyCreate->addLimitation(
            new NewSectionLimitation(
                array( 'limitationValues' => array( $sectionId ) )
            )
        );

        $role = $roleService->addPolicy(
            $roleService->loadRoleByIdentifier( 'Editor' ),
            $policyCreate
        );
        $role = $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct( 'section', 'view' )
        );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();
        $contentInfo = $contentService->loadContentInfo( $contentId );

        $sectionService = $repository->getSectionService();
        $sectionService->assignSection(
            $contentInfo,
            $sectionService->loadSection( $otherSectionId )
        );
        /* END: Use Case */
    }
}
