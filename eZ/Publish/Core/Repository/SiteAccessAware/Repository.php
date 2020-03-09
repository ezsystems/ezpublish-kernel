<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\FieldTypeService as FieldTypeServiceInterface;
use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface;
use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;

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

    public function sudo(callable $callback, ?RepositoryInterface $outerRepository = null)
    {
        return $this->repository->sudo($callback, $outerRepository ?? $this);
    }

    public function getContentService(): ContentServiceInterface
    {
        return $this->contentService;
    }

    public function getContentLanguageService(): LanguageServiceInterface
    {
        return $this->languageService;
    }

    public function getContentTypeService(): ContentTypeServiceInterface
    {
        return $this->contentTypeService;
    }

    public function getLocationService(): LocationServiceInterface
    {
        return $this->locationService;
    }

    public function getTrashService(): TrashServiceInterface
    {
        return $this->trashService;
    }

    public function getSectionService(): SectionServiceInterface
    {
        return $this->sectionService;
    }

    public function getUserService(): UserServiceInterface
    {
        return $this->userService;
    }

    public function getURLAliasService(): URLAliasServiceInterface
    {
        return $this->urlAliasService;
    }

    public function getURLWildcardService(): URLWildcardServiceInterface
    {
        return $this->repository->getURLWildcardService();
    }

    public function getObjectStateService(): ObjectStateServiceInterface
    {
        return $this->objectStateService;
    }

    public function getRoleService(): RoleServiceInterface
    {
        return $this->repository->getRoleService();
    }

    public function getSearchService(): SearchServiceInterface
    {
        return $this->searchService;
    }

    public function getFieldTypeService(): FieldTypeServiceInterface
    {
        return $this->repository->getFieldTypeService();
    }

    public function getPermissionResolver(): PermissionResolverInterface
    {
        return $this->repository->getPermissionResolver();
    }

    public function getURLService(): URLServiceInterface
    {
        return $this->repository->getURLService();
    }

    public function getBookmarkService(): BookmarkServiceInterface
    {
        return $this->repository->getBookmarkService();
    }

    public function getNotificationService(): NotificationServiceInterface
    {
        return $this->repository->getNotificationService();
    }

    public function getUserPreferenceService(): UserPreferenceServiceInterface
    {
        return $this->repository->getUserPreferenceService();
    }

    public function beginTransaction(): void
    {
        $this->repository->beginTransaction();
    }

    public function commit(): void
    {
        $this->repository->commit();
    }

    public function rollback(): void
    {
        $this->repository->rollback();
    }
}
