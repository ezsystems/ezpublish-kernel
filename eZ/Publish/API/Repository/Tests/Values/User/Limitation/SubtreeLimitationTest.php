<?php
/**
 * File containing the SubtreeLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;
use eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation
 * @group integration
 * @group limitation
 */
class SubtreeLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a combination of SubtreeLimitation, SectionLimitation and
     * the ContentTypeLimitation.
     *
     * @return  void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
     * @see eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation
     */
    public function testSubtreeLimitationAllow()
    {
        $repository = $this->getRepository();

        $userGroupId = $this->generateId( 'content', 13 );
        /* BEGIN: Use Case */
        $subtree = '/1/5/';

        $this->prepareLimitation( $subtree );

        $userService = $repository->getUserService();
        $contentService = $repository->getContentService();

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'name', 'eZ Editors' );

        $userGroup = $userService->loadUserGroup( $userGroupId );

        $groupUpdate = $userService->newUserGroupUpdateStruct();
        $groupUpdate->contentUpdateStruct = $contentUpdate;

        $userService->updateUserGroup( $userGroup, $groupUpdate );
        /* END: Use Case */

        $this->assertEquals(
            'eZ Editors',
            $userService->loadUserGroup( $userGroupId )
                ->getFieldValue( 'name' )
                ->text
        );
    }

    /**
     * Tests a combination of SubtreeLimitation, SectionLimitation and
     * the ContentTypeLimitation.
     *
     * @return  void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ContentTypeLimitation
     * @see eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
     * @see eZ\Publish\API\Repository\Values\User\Limitation\SubtreeLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testSubtreeLimitationForbid()
    {
        $repository = $this->getRepository();

        $userGroupId = $this->generateId( 'content', 13 );

        /* BEGIN: Use Case */
        $subtree = '/1/5/14/';

        $this->prepareLimitation( $subtree );

        $userService = $repository->getUserService();

        // This call will fail with an UnauthorizedException
        $userService->loadUserGroup( $userGroupId );
        /* END: Use Case */
    }

    /**
     * Prepares the Subtree limitation for the test user.
     *
     * @param string $subtree
     * @return void
     * @throws \ErrorException
     */
    protected function prepareLimitation( $subtree )
    {
        $repository = $this->getRepository();

        $userTypeId = $this->generateId( 'contentType', 4 );
        $groupTypeId = $this->generateId( 'contentType', 3 );

        $standardSectionId = $this->generateId( 'section', 1 );
        $userSectionId = $this->generateId( 'section', 2 );

        /* BEGIN: Inline */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $editPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'read' != $policy->function )
            {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if ( null === $editPolicy )
        {
            throw new \ErrorException( 'No content:read policy found.' );
        }

        // Give read access for the user section
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new SectionLimitation(
                array(
                    'limitationValues' => array(
                        $standardSectionId,
                        $userSectionId
                    )
                )
            )
        );
        $roleService->updatePolicy( $editPolicy, $policyUpdate );

        // Allow subtree access and user+user-group edit
        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'edit' );
        $policyCreate->addLimitation(
            new ContentTypeLimitation(
                array( 'limitationValues' => array( $userTypeId, $groupTypeId ) )
            )
        );
        $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser(
            $role,
            $user,
            new SubtreeLimitation(
                array( 'limitationValues' => array( $subtree ) )
            )
        );

        $repository->setCurrentUser( $user );
        /* END: Inline */
    }
}
