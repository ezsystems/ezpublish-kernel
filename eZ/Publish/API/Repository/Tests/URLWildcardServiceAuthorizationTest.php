<?php
/**
 * File containing the URLWildcardServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the URLWildcardService.
 *
 * @see eZ\Publish\API\Repository\URLWildcardService
 * @group integration
 * @group authorization
 */
class URLWildcardServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the create() method.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\URLWildcard
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */

        $userService = $repository->getUserService();
        $urlWildcardService = $repository->getURLWildcardService();

        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with an UnauthorizedException
        $urlWildcardService->create( '/articles/*', '/content/{1}' );
        /* END: Use Case */
    }

    /**
     * Test for the remove() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\URLWildcardService::remove()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testRemove
     */
    public function testRemoveThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService = $repository->getUserService();
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcardId = $urlWildcardService->create( '/articles/*', '/content/{1}' )->id;

        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // Load newly created url wildcard
        $urlWildcard = $urlWildcardService->load( $urlWildcardId );

        // This call will fail with an UnauthorizedException
        $urlWildcardService->remove( $urlWildcard );
        /* END: Use Case */
    }
}
