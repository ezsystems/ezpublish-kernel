<?php
/**
 * File containing the ParentUserGroupLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation
 * @group integration
 * @group limitation
 */
class ParentUserGroupLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a ParentUserGroupLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation
     */
    public function testParentUserGroupLimitationAllow()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $parentUserGroupId = $this->generateId( 'location', 4 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-GB' );
        $userGroupCreate->setField( 'name', 'Shared wiki' );

        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $userService->loadUserGroup(
                $parentUserGroupId
            )
        );

        // Assign system user and example user to same group
        $userService->assignUserToUserGroup( $user, $userGroup );
        $userService->assignUserToUserGroup( $repository->getCurrentUser(), $userGroup );

        $roleService = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentUserGroupLimitation(
                array(
                    'limitationValues' => array( true )
                )
            )
        );

        $role = $roleService->addPolicy(
            $roleService->loadRoleByIdentifier( 'Editor' ),
            $policyCreate
        );

        $role = $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct( 'content', 'read' )
        );

        $roleService->assignRoleToUserGroup( $role, $userGroup );

        $repository->setCurrentUser( $user );

        $draft = $this->createWikiPageDraft();
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue( 'title' )->text
        );
    }

    /**
     * Tests a ParentUserGroupLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ParentUserGroupLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testParentUserGroupLimitationForbid()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $parentUserGroupId = $this->generateId( 'location', 4 );
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-GB' );
        $userGroupCreate->setField( 'name', 'Shared wiki' );

        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $userService->loadUserGroup(
                $parentUserGroupId
            )
        );

        // Assign only example user to new group
        $userService->assignUserToUserGroup( $user, $userGroup );

        $roleService = $repository->getRoleService();

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $policyCreate->addLimitation(
            new ParentUserGroupLimitation(
                array(
                    'limitationValues' => array( true )
                )
            )
        );

        $role = $roleService->addPolicy(
            $roleService->loadRoleByIdentifier( 'Editor' ),
            $policyCreate
        );

        $role = $roleService->addPolicy(
            $role,
            $roleService->newPolicyCreateStruct( 'content', 'read' )
        );

        $roleService->assignRoleToUserGroup( $role, $userGroup );

        $repository->setCurrentUser( $user );

        $this->createWikiPageDraft();
        /* END: Use Case */
    }
}
