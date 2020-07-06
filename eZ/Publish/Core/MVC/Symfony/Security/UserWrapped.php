<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security;

use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as CoreUserInterface;

/**
 * This class represents a UserWrapped object.
 *
 * It's used when working with multiple user providers
 *
 * It has two properties:
 *     - wrappedUser: containing the originally matched user.
 *     - apiUser: containing the API User (the one from the eZ Repository )
 */
class UserWrapped implements ReferenceUserInterface, EquatableInterface
{
    /** @var \Symfony\Component\Security\Core\User\UserInterface */
    private $wrappedUser;

    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $apiUser;

    /** @var \eZ\Publish\API\Repository\Values\User\UserReference */
    private $apiUserReference;

    public function __construct(CoreUserInterface $wrappedUser, APIUser $apiUser)
    {
        $this->setWrappedUser($wrappedUser);
        $this->apiUser = $apiUser;
        $this->apiUserReference = new UserReference($apiUser->getUserId());
    }

    public function __toString()
    {
        return $this->wrappedUser->getUsername();
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     */
    public function setAPIUser(APIUser $apiUser)
    {
        $this->apiUser = $apiUser;
        $this->apiUserReference = new UserReference($apiUser->getUserId());
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    public function getAPIUser()
    {
        if (!$this->apiUser instanceof APIUser) {
            throw new \LogicException(
                'Attempted to get APIUser before it has been set by UserProvider, APIUser is not serialized to session'
            );
        }

        return $this->apiUser;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\User\UserReference
     */
    public function getAPIUserReference(): APIUserReference
    {
        return $this->apiUserReference;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $wrappedUser
     *
     * @throws InvalidArgumentException If $wrappedUser is instance of self or User to avoid duplicated APIUser in
     *     session.
     */
    public function setWrappedUser(CoreUserInterface $wrappedUser)
    {
        if ($wrappedUser instanceof self) {
            throw new InvalidArgumentException('Injecting UserWrapped in itself is not allowed to avoid recursion');
        } elseif ($wrappedUser instanceof User) {
            throw new InvalidArgumentException('Injecting a User into UserWrapped causes duplication of APIUser, which should be avoided for session serialization');
        }

        $this->wrappedUser = $wrappedUser;
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getWrappedUser()
    {
        return $this->wrappedUser;
    }

    public function getRoles()
    {
        return $this->wrappedUser->getRoles();
    }

    public function getPassword()
    {
        return $this->wrappedUser->getPassword();
    }

    public function getSalt()
    {
        return $this->wrappedUser->getSalt();
    }

    public function getUsername()
    {
        return $this->wrappedUser->getUsername();
    }

    public function eraseCredentials()
    {
        $this->wrappedUser->eraseCredentials();
    }

    public function isEqualTo(CoreUserInterface $user)
    {
        if ($user instanceof self) {
            $user = $user->wrappedUser;
        }

        return $this->wrappedUser instanceof EquatableInterface ? $this->wrappedUser->isEqualTo($user) : true;
    }

    /*
    * Make sure we don't serialize the whole API user object given it's a full fledged api content object. We set
    * (& either way refresh) the user object in \eZ\Publish\Core\MVC\Symfony\Security\User\Provider->refreshUser()
    * when object wakes back up from session.
    *
    * @return array
    */
    public function __sleep(): array
    {
        return ['wrappedUser', 'apiUserReference'];
    }
}
