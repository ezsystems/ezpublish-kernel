<?php

/**
 * File containing the URLAliasServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;

class URLAliasServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\API\Repository\URLAliasService::createUrlAlias()
     * @depends \eZ\Publish\API\Repository\Tests\URLAliasServiceTest::testCreateUrlAlias
     */
    public function testCreateUrlAliasThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        $parentLocationId = $this->generateId('location', 2);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        // $locationId is the ID of an existing location
        $userService = $repository->getUserService();
        $urlAliasService = $repository->getURLAliasService();
        $locationService = $repository->getLocationService();

        $content = $this->createFolder(['eng-GB' => 'Foo'], $parentLocationId);
        $location = $locationService->loadLocation($content->contentInfo->mainLocationId);

        $anonymousUser = $userService->loadUser($anonymousUserId);
        $repository->getPermissionResolver()->setCurrentUserReference($anonymousUser);

        $this->expectException(UnauthorizedException::class);
        $urlAliasService->createUrlAlias($location, '/Home/My-New-Site', 'eng-US');
        /* END: Use Case */
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @covers \eZ\Publish\API\Repository\URLAliasService::createGlobalUrlAlias()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends \eZ\Publish\API\Repository\Tests\URLAliasServiceTest::testCreateGlobalUrlAlias
     */
    public function testCreateGlobalUrlAliasThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $urlAliasService = $repository->getURLAliasService();

        $anonymousUser = $userService->loadUser($anonymousUserId);
        $repository->getPermissionResolver()->setCurrentUserReference($anonymousUser);

        // This call will fail with an UnauthorizedException
        $urlAliasService->createGlobalUrlAlias('module:content/search?SearchText=eZ', '/Home/My-New-Site', 'eng-US');
        /* END: Use Case */
    }

    /**
     * Test for the removeAliases() method.
     *
     * @covers \eZ\Publish\API\Repository\URLAliasService::removeAliases()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends \eZ\Publish\API\Repository\Tests\URLAliasServiceTest::testRemoveAliases
     */
    public function testRemoveAliasesThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $anonymousUserId = $this->generateId('user', 10);

        $locationService = $repository->getLocationService();
        $someLocation = $locationService->loadLocation(
            $this->generateId('location', 12)
        );

        /* BEGIN: Use Case */
        // $someLocation contains a location with automatically generated
        // aliases assigned
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        $urlAliasService = $repository->getURLAliasService();
        $userService = $repository->getUserService();

        $anonymousUser = $userService->loadUser($anonymousUserId);
        $repository->getPermissionResolver()->setCurrentUserReference($anonymousUser);

        $initialAliases = $urlAliasService->listLocationAliases($someLocation);

        // This call will fail with an UnauthorizedException
        $urlAliasService->removeAliases($initialAliases);
        /* END: Use Case */
    }
}
