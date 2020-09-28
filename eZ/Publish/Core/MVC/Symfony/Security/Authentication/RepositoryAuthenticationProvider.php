<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Authentication;

use eZ\Publish\API\Repository\Exceptions\PasswordInUnsupportedFormatException;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as EzUserInterface;
use eZ\Publish\Core\Repository\User\Exception\UnsupportedPasswordHashType;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;

class RepositoryAuthenticationProvider extends DaoAuthenticationProvider
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    public function setPermissionResolver(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
    }

    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        if (!$user instanceof EzUserInterface) {
            return parent::checkAuthentication($user, $token);
        }

        $apiUser = $user->getAPIUser();

        // $currentUser can either be an instance of UserInterface or just the username (e.g. during form login).
        /** @var EzUserInterface|string $currentUser */
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getAPIUser()->passwordHash !== $apiUser->passwordHash) {
                throw new BadCredentialsException('The credentials were changed in another session.');
            }

            $apiUser = $currentUser->getAPIUser();
        } else {
            $credentialsValid = $this->userService->checkUserCredentials($apiUser, $token->getCredentials());

            if (!$credentialsValid) {
                throw new BadCredentialsException('Invalid credentials', 0);
            }
        }

        // Finally inject current user in the Repository
        $this->permissionResolver->setCurrentUserReference($apiUser);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\PasswordInUnsupportedFormatException
     */
    public function authenticate(TokenInterface $token)
    {
        try {
            return parent::authenticate($token);
        } catch (UnsupportedPasswordHashType $exception) {
            throw new PasswordInUnsupportedFormatException($exception);
        }
    }
}
