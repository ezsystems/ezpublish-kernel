<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference;

class Repository implements RepositoryInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $innerRepository;

    /**
     * @param \eZ\Publish\API\Repository\Repository $innerRepository
     */
    public function __construct(RepositoryInterface $innerRepository)
    {
        $this->innerRepository = $innerRepository;
    }

    public function getCurrentUser()
    {
        return $this->innerRepository->getCurrentUser();
    }

    public function getCurrentUserReference()
    {
        return $this->innerRepository->getCurrentUserReference();
    }

    public function setCurrentUser(UserReference $user)
    {
        return $this->innerRepository->setCurrentUser($user);
    }

    public function sudo(\Closure $callback)
    {
        return $this->innerRepository->sudo($callback, $this);
    }

    public function hasAccess($module, $function, UserReference $user = null)
    {
        return $this->innerRepository->hasAccess($module, $function, $user);
    }

    public function canUser($module, $function, ValueObject $object, $targets = null)
    {
        return $this->innerRepository->canUser($module, $function, $object, $targets);
    }

    public function getContentService()
    {
        return $this->innerRepository->getContentService();
    }

    public function getContentLanguageService()
    {
        return $this->innerRepository->getContentLanguageService();
    }

    public function getContentTypeService()
    {
        return $this->innerRepository->getContentTypeService();
    }

    public function getLocationService()
    {
        return $this->innerRepository->getLocationService();
    }

    public function getTrashService()
    {
        return $this->innerRepository->getTrashService();
    }

    public function getSectionService()
    {
        return $this->innerRepository->getSectionService();
    }

    public function getUserService()
    {
        return $this->innerRepository->getUserService();
    }

    public function getURLAliasService()
    {
        return $this->innerRepository->getURLAliasService();
    }

    public function getURLWildcardService()
    {
        return $this->innerRepository->getURLWildcardService();
    }

    public function getObjectStateService()
    {
        return $this->innerRepository->getObjectStateService();
    }

    public function getRoleService()
    {
        return $this->innerRepository->getRoleService();
    }

    public function getSearchService()
    {
        return $this->innerRepository->getSearchService();
    }

    public function getFieldTypeService()
    {
        return $this->innerRepository->getFieldTypeService();
    }

    public function beginTransaction()
    {
        return $this->innerRepository->beginTransaction();
    }

    public function commit()
    {
        return $this->innerRepository->commit();
    }

    public function rollback()
    {
        return $this->innerRepository->rollback();
    }

    public function commitEvent($event)
    {
        return $this->innerRepository->commitEvent($event);
    }

    public function createDateTime($timestamp = null)
    {
        return $this->innerRepository->createDateTime($timestamp);
    }
}
