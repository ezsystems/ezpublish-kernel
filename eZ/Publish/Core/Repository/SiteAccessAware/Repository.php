<?php

/**
 * Repository class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference;

/**
 * Repository class.
 */
class Repository implements RepositoryInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    /** @var \eZ\Publish\API\Repository\SectionService */
    protected $sectionService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    protected $searchService;

    /** @var \eZ\Publish\API\Repository\UserService */
    protected $userService;

    /** @var \eZ\Publish\API\Repository\LanguageService */
    protected $languageService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    protected $locationService;

    /** @var \eZ\Publish\API\Repository\TrashService */
    protected $trashService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentTypeService;

    /** @var \eZ\Publish\API\Repository\ObjectStateService */
    protected $objectStateService;

    /** @var \eZ\Publish\API\Repository\URLAliasService */
    protected $urlAliasService;

    /** @var \eZ\Publish\Core\Repository\NotificationService */
    protected $notificationService;

    /**
     * Construct repository object from aggregated repository.
     */
    public function __construct(
        RepositoryInterface $repository,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        ObjectStateService $objectStateService,
        URLAliasService $urlAliasService,
        UserService $userService,
        SearchService $searchService,
        SectionService $sectionService,
        TrashService $trashService,
        LocationService $locationService,
        LanguageService $languageService,
        NotificationService $notificationService
    ) {
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->objectStateService = $objectStateService;
        $this->urlAliasService = $urlAliasService;
        $this->userService = $userService;
        $this->searchService = $searchService;
        $this->sectionService = $sectionService;
        $this->trashService = $trashService;
        $this->locationService = $locationService;
        $this->languageService = $languageService;
        $this->notificationService = $notificationService;
    }

    public function getCurrentUser()
    {
        return $this->repository->getCurrentUser();
    }

    public function getCurrentUserReference()
    {
        return $this->repository->getCurrentUserReference();
    }

    public function setCurrentUser(UserReference $user)
    {
        return $this->repository->setCurrentUser($user);
    }

    public function sudo(callable $callback, RepositoryInterface $outerRepository = null)
    {
        return $this->repository->sudo($callback, $outerRepository ?? $this);
    }

    public function hasAccess($module, $function, UserReference $user = null)
    {
        return $this->repository->hasAccess($module, $function, $user);
    }

    public function canUser($module, $function, ValueObject $object, $targets = null)
    {
        return $this->repository->canUser($module, $function, $object, $targets);
    }

    public function getContentService()
    {
        return $this->contentService;
    }

    public function getContentLanguageService()
    {
        return $this->languageService;
    }

    public function getContentTypeService()
    {
        return $this->contentTypeService;
    }

    public function getLocationService()
    {
        return $this->locationService;
    }

    public function getTrashService()
    {
        return $this->trashService;
    }

    public function getSectionService()
    {
        return $this->sectionService;
    }

    public function getUserService()
    {
        return $this->userService;
    }

    public function getURLAliasService()
    {
        return $this->urlAliasService;
    }

    public function getURLWildcardService()
    {
        return $this->repository->getURLWildcardService();
    }

    public function getObjectStateService()
    {
        return $this->objectStateService;
    }

    public function getRoleService()
    {
        return $this->repository->getRoleService();
    }

    public function getSearchService()
    {
        return $this->searchService;
    }

    public function getFieldTypeService()
    {
        return $this->repository->getFieldTypeService();
    }

    public function getPermissionResolver()
    {
        return $this->repository->getPermissionResolver();
    }

    public function getURLService()
    {
        return $this->repository->getURLService();
    }

    public function getBookmarkService()
    {
        return $this->repository->getBookmarkService();
    }

    public function getNotificationService()
    {
        return $this->repository->getNotificationService();
    }

    public function getUserPreferenceService()
    {
        return $this->repository->getUserPreferenceService();
    }

    public function beginTransaction()
    {
        return $this->repository->beginTransaction();
    }

    public function commit()
    {
        return $this->repository->commit();
    }

    public function rollback()
    {
        return $this->repository->rollback();
    }
}
