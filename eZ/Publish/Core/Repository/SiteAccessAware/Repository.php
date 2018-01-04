<?php

/**
 * Repository class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\URLService;
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

    /** @var \eZ\Publish\API\Repository\RoleService */
    protected $roleService;

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

    /** @var \eZ\Publish\API\Repository\FieldTypeService */
    protected $fieldTypeService;

    /** @var \eZ\Publish\API\Repository\URLAliasService */
    protected $urlAliasService;

    /** @var \eZ\Publish\API\Repository\URLService */
    protected $urlService;

    /** @var \eZ\Publish\API\Repository\URLWildcardService */
    protected $urlWildcardService;

    /**
     * Construct repository object from aggregated repository.
     */
    public function __construct(
        RepositoryInterface $repository,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FieldTypeService $fieldTypeService,
        RoleService $roleService,
        ObjectStateService $objectStateService,
        URLAliasService $urlAliasService,
        URLService $urlService,
        URLWildcardService $urlWildcardService,
        UserService $userService,
        SearchService $searchService,
        SectionService $sectionService,
        TrashService $trashService,
        LocationService $locationService,
        LanguageService $languageService
    ) {
        $this->repository = $repository;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeService = $fieldTypeService;
        $this->roleService = $roleService;
        $this->objectStateService = $objectStateService;
        $this->urlAliasService = $urlAliasService;
        $this->urlService = $urlService;
        $this->urlWildcardService = $urlWildcardService;
        $this->userService = $userService;
        $this->searchService = $searchService;
        $this->sectionService = $sectionService;
        $this->trashService = $trashService;
        $this->locationService = $locationService;
        $this->languageService = $languageService;

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

    public function sudo(\Closure $callback)
    {
        return $this->repository->sudo($callback, $this);
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
        return $this->urlWildcardService;
    }

    public function getObjectStateService()
    {
        return $this->objectStateService;
    }

    public function getRoleService()
    {
        return $this->roleService;
    }

    public function getSearchService()
    {
        return $this->searchService;
    }

    public function getFieldTypeService()
    {
        return $this->fieldTypeService;
    }

    public function getPermissionResolver()
    {
        return $this->repository->getPermissionResolver();
    }

    public function getURLService()
    {
        return $this->urlService;
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
