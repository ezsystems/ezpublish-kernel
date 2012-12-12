<?php
/**
 * File containing the StateLimitationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\Limitation\StateLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\StateLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\StateLimitation
 * @group integration
 * @group limitation
 */
class StateLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a StateLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\StateLimitation
     * @throws \ErrorException
     */
    public function testStateLimitationAllow()
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
            if ( 'content' != $policy->module || 'versionremove' != $policy->function )
            {
                continue;
            }
            $removePolicy = $policy;
            break;
        }

        if ( null === $removePolicy )
        {
            throw new \ErrorException( 'No content:versionremove policy found.' );
        }

        // Only allow Draft deletes
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new StateLimitation(
                array(
                    'limitationValues' => array(
                        VersionInfo::STATUS_DRAFT
                    )
                )
            )
        );
        $roleService->updatePolicy( $removePolicy, $policyUpdate );

        // Allow user to create everything
        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $draft = $this->createWikiPageDraft();

        $contentService->deleteVersion( $draft->versionInfo );
        /* END: Use Case */

        $this->setExpectedException( '\\eZ\\Publish\\API\\Repository\\Exceptions\\NotFoundException' );
        $contentService->loadContent( $draft->id );
    }

    /**
     * Tests a StateLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\StateLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testStateLimitationForbid()
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
            if ( 'content' != $policy->module || 'versionremove' != $policy->function )
            {
                continue;
            }
            $removePolicy = $policy;
            break;
        }

        if ( null === $removePolicy )
        {
            throw new \ErrorException( 'No content:versionremove policy found.' );
        }

        // Only allow Draft deletes
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new StateLimitation(
                array(
                    'limitationValues' => array(
                        VersionInfo::STATUS_ARCHIVED
                    )
                )
            )
        );
        $roleService->updatePolicy( $removePolicy, $policyUpdate );

        // Allow user to create everything
        $policyCreate = $roleService->newPolicyCreateStruct( 'content', 'create' );
        $roleService->addPolicy( $role, $policyCreate );

        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $draft = $this->createWikiPageDraft();

        $contentService->deleteVersion( $draft->versionInfo );
        /* END: Use Case */
    }
}
