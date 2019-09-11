<?php

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\BookmarkService as BookmarkServiceInterface;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\FieldTypeService as FieldTypeServiceInterface;
use eZ\Publish\API\Repository\LanguageService as LanguageServiceInterface;
use eZ\Publish\API\Repository\LocationService as LocationServiceInterface;
use eZ\Publish\API\Repository\NotificationService as NotificationServiceInterface;
use eZ\Publish\API\Repository\ObjectStateService as ObjectStateServiceInterface;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\RoleService as RoleServiceInterface;
use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\SectionService as SectionServiceInterface;
use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\URLAliasService as URLAliasServiceInterface;
use eZ\Publish\API\Repository\URLService as URLServiceInterface;
use eZ\Publish\API\Repository\URLWildcardService as URLWildcardServiceInterface;
use eZ\Publish\API\Repository\UserPreferenceService as UserPreferenceServiceInterface;
use eZ\Publish\API\Repository\UserService as UserServiceInterface;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\API\Repository\Values\ValueObject;

final class Repository implements RepositoryInterface
{
    /** @var \eZ\Publish\API\Repository\Repository */
    private $repository;

    /** @var \eZ\Publish\API\Repository\BookmarkService */
    private $bookmarkService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\API\Repository\FieldTypeService */
    private $fieldTypeService;

    /** @var \eZ\Publish\API\Repository\LanguageService */
    private $languageService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\NotificationService */
    private $notificationService;

    /** @var \eZ\Publish\API\Repository\ObjectStateService */
    private $objectStateService;

    /** @var \eZ\Publish\API\Repository\RoleService */
    private $roleService;

    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    /** @var \eZ\Publish\API\Repository\SectionService */
    private $sectionService;

    /** @var \eZ\Publish\API\Repository\TrashService */
    private $trashService;

    /** @var \eZ\Publish\API\Repository\URLAliasService */
    private $urlAliasService;

    /** @var \eZ\Publish\API\Repository\URLService */
    private $urlService;

    /** @var \eZ\Publish\API\Repository\URLWildcardService */
    private $urlWildcardService;

    /** @var \eZ\Publish\API\Repository\UserPreferenceService */
    private $userPreferenceService;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    public function __construct(
        RepositoryInterface $repository,
        BookmarkServiceInterface $bookmarkService,
        ContentServiceInterface $contentService,
        ContentTypeServiceInterface $contentTypeService,
        FieldTypeServiceInterface $fieldTypeService,
        LanguageServiceInterface $languageService,
        LocationServiceInterface $locationService,
        NotificationServiceInterface $notificationService,
        ObjectStateServiceInterface $objectStateService,
        RoleServiceInterface $roleService,
        SearchServiceInterface $searchService,
        SectionServiceInterface $sectionService,
        TrashServiceInterface $trashService,
        URLAliasServiceInterface $urlAliasService,
        URLServiceInterface $urlService,
        URLWildcardServiceInterface $urlWildcardService,
        UserPreferenceServiceInterface $userPreferenceService,
        UserServiceInterface $userService
    ) {
        $this->repository = $repository;
        $this->bookmarkService = $bookmarkService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeService = $fieldTypeService;
        $this->languageService = $languageService;
        $this->locationService = $locationService;
        $this->notificationService = $notificationService;
        $this->objectStateService = $objectStateService;
        $this->roleService = $roleService;
        $this->searchService = $searchService;
        $this->sectionService = $sectionService;
        $this->trashService = $trashService;
        $this->urlAliasService = $urlAliasService;
        $this->urlService = $urlService;
        $this->urlWildcardService = $urlWildcardService;
        $this->userPreferenceService = $userPreferenceService;
        $this->userService = $userService;
    }

    public function hasAccess($module, $function, UserReference $user = null)
    {
        return $this->repository->hasAccess($module, $function, $user);
    }

    public function canUser($module, $function, ValueObject $object, $targets = null): bool
    {
        return $this->repository->canUser($module, $function, $object, $targets);
    }

    public function sudo(callable $callback, RepositoryInterface $outerRepository = null)
    {
        return $this->repository->sudo($callback, $outerRepository);
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

    public function getPermissionResolver(): PermissionResolver
    {
        return $this->repository->getPermissionResolver();
    }

    public function getBookmarkService(): BookmarkServiceInterface
    {
        return $this->bookmarkService;
    }

    public function getContentService(): ContentServiceInterface
    {
        return $this->contentService;
    }

    public function getContentTypeService(): ContentTypeServiceInterface
    {
        return $this->contentTypeService;
    }

    public function getFieldTypeService(): FieldTypeServiceInterface
    {
        return $this->fieldTypeService;
    }

    public function getContentLanguageService(): LanguageServiceInterface
    {
        return $this->languageService;
    }

    public function getLocationService(): LocationServiceInterface
    {
        return $this->locationService;
    }

    public function getNotificationService(): NotificationServiceInterface
    {
        return $this->notificationService;
    }

    public function getObjectStateService(): ObjectStateServiceInterface
    {
        return $this->objectStateService;
    }

    public function getRoleService(): RoleServiceInterface
    {
        return $this->roleService;
    }

    public function getSearchService(): SearchServiceInterface
    {
        return $this->searchService;
    }

    public function getSectionService(): SectionServiceInterface
    {
        return $this->sectionService;
    }

    public function getTrashService(): TrashServiceInterface
    {
        return $this->trashService;
    }

    public function getURLAliasService(): URLAliasServiceInterface
    {
        return $this->urlAliasService;
    }

    public function getURLService(): URLServiceInterface
    {
        return $this->urlService;
    }

    public function getURLWildcardService(): URLWildcardServiceInterface
    {
        return $this->urlWildcardService;
    }

    public function getUserPreferenceService(): UserPreferenceServiceInterface
    {
        return $this->userPreferenceService;
    }

    public function getUserService(): UserServiceInterface
    {
        return $this->userService;
    }
}
