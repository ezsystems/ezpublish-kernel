<?php
/**
 * File containing the ObjectStateLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation
 * @group integration
 * @group limitation
 */
class ObjectStateLimitationTest extends BaseLimitationTest
{
    /**
     * Tests a ObjectStateLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation
     * @throws \ErrorException
     */
    public function testObjectStateLimitationAllow()
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

        // Only allow deletion of content with default state
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ObjectStateLimitation(
                array(
                    'limitationValues' => array(
                        // 'not_locked' state
                        2
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
     * Tests a ObjectStateLimitation
     *
     * @return void
     * @see eZ\Publish\API\Repository\Values\User\Limitation\ObjectStateLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testObjectStateLimitationForbid()
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

        // Only allow deletion of content with default state
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new ObjectStateLimitation(
                array(
                    'limitationValues' => array(
                        // 'locked' state
                        1
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
