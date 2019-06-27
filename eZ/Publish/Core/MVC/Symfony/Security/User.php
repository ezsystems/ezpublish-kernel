<?php

/**
 * File containing the User class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security;

use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class User implements ReferenceUserInterface, EquatableInterface
{
    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $user;

    /** @var \eZ\Publish\API\Repository\Values\User\UserReference */
    private $reference;

    /** @var array */
    private $roles;

    public function __construct(APIUser $user = null, array $roles = [])
    {
        $this->user = $user;
        $this->reference = new UserReference($user ? $user->getUserId() : null);
        $this->roles = $roles;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array( 'ROLE_USER' );
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->getAPIUser()->passwordHash;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getAPIUser()->login;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\UserReference
     */
    public function getAPIUserReference()
    {
        return $this->reference;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getAPIUser()
    {
        if (!$this->user instanceof APIUser) {
            throw new \LogicException(
                'Attempts to get APIUser before it has been set by UserProvider, APIUser is not serialized to session'
            );
        }

        return $this->user;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     */
    public function setAPIUser(APIUser $user)
    {
        $this->user = $user;
        $this->reference = new UserReference($user->getUserId());
    }

    public function isEqualTo(BaseUserInterface $user)
    {
        // Check for the lighter ReferenceUserInterface first
        if ($user instanceof ReferenceUserInterface) {
            return $user->getAPIUserReference()->getUserId() === $this->reference->getUserId();
        } elseif ($user instanceof UserInterface) {
            return $user->getAPIUser()->getUserId() === $this->reference->getUserId();
        }

        return false;
    }

    public function __toString()
    {
        return $this->getAPIUser()->contentInfo->name;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return $this->getAPIUser()->enabled;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return $this->getAPIUser()->enabled;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->getAPIUser()->enabled;
    }

    /**
     * Make sure we don't serialize the whole API user object given it's a full fledged api content object. We set
     * (& either way refresh) the user object in \eZ\Publish\Core\MVC\Symfony\Security\User\Provider->refreshUser()
     * when object wakes back up from session.
     *
     * @return array
     */
    public function __sleep()
    {
        return ['reference', 'roles'];
    }
}
