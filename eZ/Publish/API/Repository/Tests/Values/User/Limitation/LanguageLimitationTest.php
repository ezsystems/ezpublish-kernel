<?php
/**
 * File containing the LanguageLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
 * @group integration
 * @group limitation
 */
class LanguageLimitationTest extends BaseLimitationTest
{

    /**
     * Test for the LanguageLimitation
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
     * @throws \ErrorException
     */
    public function testLanguageLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'content', 58 );
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

        // Only allow eng-GB content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new LanguageLimitation(
                array( 'limitationValues' => array( 'eng-GB' ) )
            )
        );
        $roleService->updatePolicy( $editPolicy, $policyUpdate );

        $roleService->assignRoleToUser( $role, $user );

        $contentService = $repository->getContentService();

        $repository->setCurrentUser( $user );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'name', 'Contact Me' );

        $draft = $contentService->createContentDraft(
            $contentService->loadContentInfo( $contentId )
        );

        // Update content object
        $draft = $contentService->updateContent(
            $draft->versionInfo,
            $contentUpdate
        );

        $contentService->publishVersion( $draft->versionInfo );
        /* END: Use Case */

        $this->assertEquals(
            'Contact Me',
            $contentService->loadContent( $contentId )
                ->getFieldValue( 'name' )->text
        );
    }

    /**
     * Test for the LanguageLimitation
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \ErrorException
     */
    public function testLanguageLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId( 'content', 58 );
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

        // Only allow eng-US content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new LanguageLimitation(
                array( 'limitationValues' => array( 'eng-US' ) )
            )
        );
        $roleService->updatePolicy( $editPolicy, $policyUpdate );

        $roleService->assignRoleToUser( $role, $user );

        $contentService = $repository->getContentService();

        $repository->setCurrentUser( $user );

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField( 'name', 'Contact Me' );

        // This call will fail with an UnauthorizedException
        $contentService->createContentDraft(
            $contentService->loadContentInfo( $contentId )
        );
        /* END: Use Case */
    }
}
