<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Security\User;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use eZ\Publish\Core\MVC\Symfony\Security\ReferenceUserInterface;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface as CoreUserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

abstract class BaseProvider implements APIUserProviderInterface
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    protected $permissionResolver;

    /** @var \eZ\Publish\API\Repository\UserService */
    protected $userService;

    public function __construct(
        UserService $userService,
        PermissionResolver $permissionResolver
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
    }

    public function refreshUser(CoreUserInterface $user)
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        try {
            $refreshedAPIUser = $this->userService->loadUser(
                $user instanceof ReferenceUserInterface
                    ? $user->getAPIUserReference()->getUserId()
                    : $user->getAPIUser()->id
            );
            $user->setAPIUser($refreshedAPIUser);
            $this->permissionResolver->setCurrentUserReference(
                new UserReference($refreshedAPIUser->getUserId())
            );

            return $user;
        } catch (NotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        $supportedClass = 'eZ\\Publish\\Core\\MVC\\Symfony\\Security\\User';

        return $class === $supportedClass || is_subclass_of($class, $supportedClass);
    }

    /**
     * Loads a regular user object, usable by Symfony Security component, from a user object returned by Public API.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Security\User
     */
    public function loadUserByAPIUser(APIUser $apiUser)
    {
        return $this->createSecurityUser($apiUser);
    }

    /**
     * Creates user object, usable by Symfony Security component, from a user object returned by Public API.
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Security\User
     */
    protected function createSecurityUser(APIUser $apiUser): User
    {
        return new User($apiUser, ['ROLE_USER']);
    }
}
