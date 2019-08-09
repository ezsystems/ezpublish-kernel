<?php

/**
 * File containing the URLWildcardServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;

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
     *
     * @see \eZ\Publish\API\Repository\URLWildcardService::create()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testCreate
     */
    public function testCreateThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $urlWildcardService = $repository->getURLWildcardService();

        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        $this->expectException(UnauthorizedException::class);
        $urlWildcardService->create('/articles/*', '/content/{1}');
        /* END: Use Case */
    }

    /**
     * Test for the remove() method.
     *
     * @see \eZ\Publish\API\Repository\URLWildcardService::remove()
     * @depends eZ\Publish\API\Repository\Tests\URLWildcardServiceTest::testRemove
     */
    public function testRemoveThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $urlWildcardService = $repository->getURLWildcardService();

        // Create a new url wildcard
        $urlWildcardId = $urlWildcardService->create('/articles/*', '/content/{1}')->id;

        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        // Load newly created url wildcard
        $urlWildcard = $urlWildcardService->load($urlWildcardId);

        $this->expectException(UnauthorizedException::class);
        $urlWildcardService->remove($urlWildcard);
        /* END: Use Case */
    }
}
