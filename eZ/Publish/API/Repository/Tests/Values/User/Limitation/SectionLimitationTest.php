<?php
/**
 * File containing the SectionLimitationTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation;
use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation}
 * class.
 *
 * @see eZ\Publish\API\Repository\Values\User\Limitation
 * @see eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
 * @group integration
 * @group limitation
 */
class SectionLimitationTest extends BaseLimitationTest
{
    /**
     * Test for the SectionLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
     * @throws \ErrorException
     */
    public function testSectionLimitationAllow()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $readPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'read' != $policy->function )
            {
                continue;
            }
            $readPolicy = $policy;
            break;
        }

        if ( null === $readPolicy )
        {
            throw new \ErrorException( 'No content:read policy found.' );
        }

        // Only allow access to the media section
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new SectionLimitation(
                array( 'limitationValues' => array( 3 ) )
            )
        );

        $roleService->updatePolicy( $readPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // Load the images folder
        $images = $contentService->loadContentByRemoteId( 'e7ff633c6b8e0fd3531e74c6e712bead' );
        /* END: Use Case */

        $this->assertEquals(
            'Images',
            $images->getFieldValue( 'name' )->text
        );
    }

    /**
     * Test for the SectionLimitation.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\SectionLimitation
     * @throws \ErrorException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testSectionLimitationForbid()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier( 'Editor' );

        $readPolicy = null;
        foreach ( $role->getPolicies() as $policy )
        {
            if ( 'content' != $policy->module || 'read' != $policy->function )
            {
                continue;
            }
            $readPolicy = $policy;
            break;
        }

        if ( null === $readPolicy )
        {
            throw new \ErrorException( 'No content:read policy found.' );
        }

        // Give access to "Standard" and "Restricted" section
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new SectionLimitation(
                array( 'limitationValues' => array( 1, 6 ) )
            )
        );

        $roleService->updatePolicy( $readPolicy, $policyUpdate );
        $roleService->assignRoleToUser( $role, $user );

        $repository->setCurrentUser( $user );

        $contentService = $repository->getContentService();

        // This call fails with an UnauthorizedException because the current user
        // cannot access the "Media" section
        $contentService->loadContentByRemoteId( 'e7ff633c6b8e0fd3531e74c6e712bead' );
        /* END: Use Case */
    }
}
