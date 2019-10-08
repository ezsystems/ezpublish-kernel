<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Security\Tests;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\User\PasswordInfo;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\Core\MVC\Symfony\Security\UserChecker;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use eZ\Publish\Core\Repository\Values\User\User as APIUser;
use Throwable;
use DateTimeImmutable;

final class UserCheckerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $userServiceMock;

    /** @var \eZ\Publish\Core\MVC\Symfony\Security\UserChecker */
    private $userChecker;

    protected function setUp(): void
    {
        $this->userServiceMock = $this->createMock(UserService::class);
        $this->userChecker = new UserChecker($this->userServiceMock);
    }

    public function testCheckPreAuthWithEnabledUser(): void
    {
        $apiUser = new APIUser(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(),
                            ]
                        ),
                    ]
                ),
                'enabled' => true,
            ]
        );

        try {
            $this->userChecker->checkPreAuth(new User($apiUser));
        } catch (Throwable $t) {
            self::fail('Error was not expected to be raised.');
        }
    }

    public function testCheckPreAuthWithDisabledUser(): void
    {
        $apiUser = new APIUser(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(),
                            ]
                        ),
                    ]
                ),
                'enabled' => false,
            ]
        );

        $this->expectException(DisabledException::class);

        $this->userChecker->checkPreAuth(new User($apiUser));
    }

    public function testCheckPostAuthWithNonExpiredUser(): void
    {
        $apiUser = new APIUser(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(),
                            ]
                        ),
                    ]
                ),
            ]
        );

        $this->userServiceMock
            ->expects(self::once())
            ->method('getPasswordInfo')
            ->with(self::identicalTo($apiUser))
            ->willReturn(new PasswordInfo());

        try {
            $this->userChecker->checkPostAuth(new User($apiUser));
        } catch (Throwable $t) {
            self::fail('Error was not expected to be raised.');
        }
    }

    public function testCheckPostAuthWithExpiredUser(): void
    {
        $apiUser = new APIUser(
            [
                'content' => new Content(
                    [
                        'versionInfo' => new VersionInfo(
                            [
                                'contentInfo' => new ContentInfo(),
                            ]
                        ),
                    ]
                ),
            ]
        );

        $this->userServiceMock
            ->expects(self::once())
            ->method('getPasswordInfo')
            ->with(self::identicalTo($apiUser))
            ->willReturn(
                new PasswordInfo(
                    DateTimeImmutable::createFromFormat('Y-m-d', '2019-01-01')
                )
            );

        $this->expectException(CredentialsExpiredException::class);

        $this->userChecker->checkPostAuth(new User($apiUser));
    }
}
