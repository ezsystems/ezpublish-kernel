<?php

/**
 * UserService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Decorator\UserServiceDecorator;
use eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver;

/**
 * UserService for SiteAccessAware layer.
 *
 * Currently does nothing but hand over calls to aggregated service.
 */
class UserService extends UserServiceDecorator
{
    /** @var \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service.
     *
     * @param \eZ\Publish\API\Repository\UserService $service
     * @param \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver $languageResolver
     */
    public function __construct(
        UserServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        parent::__construct($service);

        $this->languageResolver = $languageResolver;
    }

    public function loadUserGroup($id, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUserGroup($id, $prioritizedLanguages);
    }

    public function loadSubUserGroups(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadSubUserGroups($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    public function loadUser($userId, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUser($userId, $prioritizedLanguages);
    }

    public function loadUserByCredentials($login, $password, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUserByCredentials($login, $password, $prioritizedLanguages);
    }

    public function loadUserByLogin($login, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUserByLogin($login, $prioritizedLanguages);
    }

    public function loadUsersByEmail($email, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUsersByEmail($email, $prioritizedLanguages);
    }

    public function loadUserGroupsOfUser(User $user, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUserGroupsOfUser($user, $offset, $limit, $prioritizedLanguages);
    }

    public function loadUsersOfUserGroup(UserGroup $userGroup, $offset = 0, $limit = 25, array $prioritizedLanguages = null)
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages);

        return $this->service->loadUsersOfUserGroup($userGroup, $offset, $limit, $prioritizedLanguages);
    }

    public function loadUserByToken($hash, array $prioritizedLanguages = null)
    {
        return $this->service->loadUserByToken(
            $hash,
            $this->languageResolver->getPrioritizedLanguages($prioritizedLanguages)
        );
    }
}
