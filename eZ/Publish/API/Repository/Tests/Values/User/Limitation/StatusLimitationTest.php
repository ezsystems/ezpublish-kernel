<?php
/**
 * File containing the StatusLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation
 * @group integration
 * @group limitation
 */
class StatusLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a StatusLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation
     */
    public function testStatusLimitationAllow()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId( 'user', 14 );
        /* BEGIN: Use Case */
        // $administratorUserId is the ID of the "Administrator" user in a eZ
        // Publish demo installation.

        // Load the user service
        $userService = $repository->getUserService();

        // Load the "Administrator" user and set it as current user
        $administratorUser = $userService->loadUser( $administratorUserId );
        $repository->setCurrentUser( $administratorUser );

        // Create a Content draft with "Administrator" user
        $draft = $this->createWikiPageDraft();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Anonymous' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'versionread' );
        $policyCreate->addLimitation(
            new StatusLimitation(
                array( 'limitationValues' => array( VersionInfo::STATUS_DRAFT ) )
            )
        );

        // Add policy to load draft versions to "Anonymous" role
        $roleService->addPolicy( $role, $policyCreate );

        // Load the user service
        $userService = $repository->getUserService();

        // Load "Anonymous User" (which has "Anonymous" role)
        $anonymousUser = $userService->loadAnonymousUser();

        // Set it as current user
        $repository->setCurrentUser( $anonymousUser );

        $contentService = $repository->getContentService();

        // Try to load Administrator draft with Anonymous User
        // This will succeed because required policy was previously set to the Anonymous role
        $loadedDraft = $contentService->loadContent(
            $draft->getVersionInfo()->getContentInfo()->id,
            null,
            $draft->getVersionInfo()->versionNo
        );
        /* END: Use Case */

        $this->assertEquals(
            'An awesome wiki page',
            $loadedDraft->getFieldValue( 'title' )->text
        );
    }

    /**
     * Tests a StatusLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\StatusLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testStatusLimitationForbid()
    {
        $repository = $this->getRepository();

        $administratorUserId = $this->generateId( 'user', 14 );
        /* BEGIN: Use Case */
        // $administratorUserId is  the ID of the "Administrator" user in a eZ
        // Publish demo installation.

        // Load the user service
        $userService = $repository->getUserService();

        // Load the "Administrator" user and set it as current user
        $administratorUser = $userService->loadUser( $administratorUserId );
        $repository->setCurrentUser( $administratorUser );

        // Create a Content draft with "Administrator" user
        $draft = $this->createWikiPageDraft();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Anonymous' );

        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'versionread' );
        $policyCreate->addLimitation(
            new StatusLimitation(
                array( 'limitationValues' => array( VersionInfo::STATUS_PUBLISHED ) )
            )
        );

        // Add policy to load published versions to "Anonymous" role
        $roleService->addPolicy( $role, $policyCreate );

        // Load the user service
        $userService = $repository->getUserService();

        // Load anonymous user (which has "Anonymous" role)
        $anonymousUser = $userService->loadAnonymousUser();

        // Set it as current user
        $repository->setCurrentUser( $anonymousUser );

        $contentService = $repository->getContentService();

        // Try to load Administrator user draft with "Anonymous User"
        // This will fail with "UnauthorizedException" because we allowed users with
        // "Anonymous" role to read only published versions
        $loadedDraft = $contentService->loadContent(
            $draft->getVersionInfo()->getContentInfo()->id,
            null,
            $draft->getVersionInfo()->versionNo
        );
        /* END: Use Case */
    }
}
