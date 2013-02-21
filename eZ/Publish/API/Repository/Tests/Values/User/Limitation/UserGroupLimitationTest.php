<?php
/**
 * File containing the UserGroupLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation
 * @group integration
 * @group limitation
 */
class UserGroupLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a UserGroupLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation
     * @throws \ErrorException if a mandatory test fixture not exists.
     */
    public function testUserGroupLimitationAllow()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroup = $this->prepareUserGroup();

        // Assign system user and example user to same group
        $userService->assignUserToUserGroup( $user, $userGroup );
        $userService->assignUserToUserGroup( $repository->getCurrentUser(), $userGroup );

        $draft = $this->prepareLimitationAndContent( $user, $userGroup );
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $draft->getFieldValue( 'title' )->text
        );
    }

    /**
     * Tests a UserGroupLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\UserGroupLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \ErrorException if a mandatory test fixture not exists.
     */
    public function testUserGroupLimitationForbid()
    {
        $repository = $this->getRepository();

        $userService = $repository->getUserService();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $userGroup = $this->prepareUserGroup();

        // Assign example user to new group
        $userService->assignUserToUserGroup( $user, $userGroup );

        $this->prepareLimitationAndContent( $user, $userGroup );
        /* END: Use Case */
    }

    /**
     * Prepares the UserGroup fixture.
     *
     * @return \eZ\Publish\API\Repository\Values\User\UserGroup
     */
    protected function prepareUserGroup()
    {
        $repository = $this->getRepository();
        $userService = $repository->getUserService();

        $parentUserGroupId = $this->generateId( 'location', 4 );
        /* BEGIN: Inline */
        $userGroupCreate = $userService->newUserGroupCreateStruct( 'eng-GB' );
        $userGroupCreate->setField( 'name', 'Shared wiki' );

        $userGroup = $userService->createUserGroup(
            $userGroupCreate,
            $userService->loadUserGroup(
                $parentUserGroupId
            )
        );
        /* END: Inline */

        return $userGroup;
    }

    /**
     * Prepares the limitation fixture.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     * @param \eZ\Publish\API\Repository\Values\User\UserGroup $userGroup
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     * @throws \ErrorException
     */
    protected function prepareLimitationAndContent( User $user, UserGroup $userGroup )
    {
        $repository = $this->getRepository();

        $contentService = $repository->getContentService();

        /* BEGIN: Inline */
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
            throw new \ErrorException(
                'Cannot find mandatory policy test fixture content::edit.'
            );
        }

        // Give read access for the user section
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new UserGroupLimitation(
                array(
                    'limitationValues' => array( true )
                )
            )
        );
        $roleService->updatePolicy( $editPolicy, $policyUpdate );

        $roleService->assignRoleToUserGroup( $role, $userGroup );

        $content = $this->createWikiPage();
        $contentId = $content->id;

        $repository->setCurrentUser( $user );

        $draft = $contentService->createContentDraft(
            $contentService->loadContentInfo( $contentId )
        );
        /* END: Inline */

        return $draft;
    }
}
