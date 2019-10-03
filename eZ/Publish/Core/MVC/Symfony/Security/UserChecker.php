<?php

declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Security;

use eZ\Publish\API\Repository\UserService;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as EzUserInterface;

final class UserChecker implements UserCheckerInterface
{
    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof EzUserInterface) {
            return;
        }

        $apiUser = $user->getAPIUser();

        if (!$apiUser->enabled) {
            throw new DisabledException();
        }

        if ($this->userService->getPasswordInfo($apiUser)->isPasswordExpired()) {
            throw new CredentialsExpiredException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
