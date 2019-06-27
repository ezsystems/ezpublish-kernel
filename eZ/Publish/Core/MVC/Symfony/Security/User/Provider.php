<?php

/**
 * File containing the user Provider class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\User;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Security\User;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use eZ\Publish\Core\MVC\Symfony\Security\ReferenceUserInterface;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface as CoreUserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class Provider implements APIUserProviderInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /**
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Loads the user for the given user ID.
     * $user can be either the user ID or an instance of \eZ\Publish\Core\MVC\Symfony\Security\User
     * (anonymous user we try to check access via SecurityContext::isGranted()).
     *
     * @param string|\eZ\Publish\Core\MVC\Symfony\Security\User $user Either the user ID to load an instance of User object. A value of -1 represents an anonymous user.
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Security\UserInterface
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($user)
    {
        try {
            // SecurityContext always tries to authenticate anonymous users when checking granted access.
            // In that case $user is an instance of \eZ\Publish\Core\MVC\Symfony\Security\User.
            // We don't need to reload the user here.
            if ($user instanceof UserInterface) {
                return $user;
            }

            return new User($this->repository->getUserService()->loadUserByLogin($user), ['ROLE_USER']);
        } catch (NotFoundException $e) {
            throw new UsernameNotFoundException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function refreshUser(CoreUserInterface $user)
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        try {
            $refreshedAPIUser = $this->repository->getUserService()->loadUser(
                $user instanceof ReferenceUserInterface ?
                $user->getAPIUserReference()->getUserId() :
                $user->getAPIUser()->id
            );
            $user->setAPIUser($refreshedAPIUser);
            $this->repository->setCurrentUser($refreshedAPIUser);

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
        return new User($apiUser, ['ROLE_USER']);
    }
}
