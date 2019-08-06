<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\API\Repository\Values\URL\URLQuery;

class URLServiceAuthorizationTest extends BaseURLServiceTest
{
    /**
     * Test for the findUrls() method.
     *
     * @see \eZ\Publish\API\Repository\URLService::findUrls
     */
    public function testFindUrlsThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $urlService = $repository->getURLService();

        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        $query = new URLQuery();
        $query->filter = new Criterion\MatchAll();

        $this->expectException(UnauthorizedException::class);
        $urlService->findUrls($query);
        /* END: Use Case */
    }

    /**
     * Test for the updateUrl() method.
     *
     * @see \eZ\Publish\API\Repository\URLService::updateUrl
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateUrlThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        $urlId = $this->generateId('url', 23);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $urlService = $repository->getURLService();

        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        $url = $urlService->loadById($urlId);
        $updateStruct = $urlService->createUpdateStruct();
        $updateStruct->url = 'https://vimeo.com/';

        // This call will fail with an UnauthorizedException
        $urlService->updateUrl($url, $updateStruct);
        /* END: Use Case */
    }

    /**
     * Test for the loadById() method.
     *
     * @see \eZ\Publish\API\Repository\URLService::loadById
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadByIdThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        $urlId = $this->generateId('url', 23);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $urlService = $repository->getURLService();

        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        // This call will fail with an UnauthorizedException
        $urlService->loadById($urlId);
        /* END: Use Case */
    }

    /**
     * Test for the loadByUrl() method.
     *
     * @see \eZ\Publish\API\Repository\URLService::loadById
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testLoadByUrlThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        $url = '/content/view/sitemap/2';

        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.

        $userService = $repository->getUserService();
        $urlService = $repository->getURLService();

        $repository->getPermissionResolver()->setCurrentUserReference($userService->loadUser($anonymousUserId));

        // This call will fail with an UnauthorizedException
        $urlService->loadByUrl($url);
        /* END: Use Case */
    }
}
