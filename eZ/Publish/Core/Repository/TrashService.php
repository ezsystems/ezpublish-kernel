<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\TrashService as TrashServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException as APIUnauthorizedException;
use eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\TrashItem as APITrashItem;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\SPI\Persistence\Content\Location\Trashed;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Trash\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\PermissionCriterionResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd as CriterionLogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot as CriterionLogicalNot;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree as CriterionSubtree;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use DateTime;
use Exception;

/**
 * Trash service, used for managing trashed content.
 */
class TrashService implements TrashServiceInterface
{
    /** @var \eZ\Publish\Core\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\SPI\Persistence\Handler */
    protected $persistenceHandler;

    /** @var array */
    protected $settings;

    /** @var \eZ\Publish\Core\Repository\Helper\NameSchemaService */
    protected $nameSchemaService;

    /** @var \eZ\Publish\API\Repository\PermissionCriterionResolver */
    private $permissionCriterionResolver;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface */
    private $proxyDomainMapper;

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param \eZ\Publish\Core\Repository\Helper\NameSchemaService $nameSchemaService
     * @param \eZ\Publish\API\Repository\PermissionCriterionResolver $permissionCriterionResolver
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface $permissionResolver
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        Handler $handler,
        Helper\NameSchemaService $nameSchemaService,
        PermissionCriterionResolver $permissionCriterionResolver,
        PermissionResolver $permissionResolver,
        ProxyDomainMapperInterface $proxyDomainMapper,
        array $settings = []
    ) {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->nameSchemaService = $nameSchemaService;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + [
            //'defaultSetting' => array(),
        ];
        $this->permissionCriterionResolver = $permissionCriterionResolver;
        $this->permissionResolver = $permissionResolver;
        $this->proxyDomainMapper = $proxyDomainMapper;
    }

    /**
     * Loads a trashed location object from its $id.
     *
     * Note that $id is identical to original location, which has been previously trashed
     *
     * @param mixed $trashItemId
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read the trashed location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the location with the given id does not exist
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem
     */
    public function loadTrashItem(int $trashItemId): APITrashItem
    {
        $spiTrashItem = $this->persistenceHandler->trashHandler()->loadTrashItem($trashItemId);
        $trash = $this->buildDomainTrashItemObject(
            $spiTrashItem,
            $this->repository->getContentService()->internalLoadContentById($spiTrashItem->contentId)
        );
        if (!$this->permissionResolver->canUser('content', 'read', $trash->getContentInfo())) {
            throw new UnauthorizedException('content', 'read');
        }

        if (!$this->permissionResolver->canUser('content', 'restore', $trash->getContentInfo())) {
            throw new UnauthorizedException('content', 'restore');
        }

        return $trash;
    }

    /**
     * Sends $location and all its children to trash and returns the corresponding trash item.
     *
     * The current user may not have access to the returned trash item, check before using it.
     * Content is left untouched.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to trash the given location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TrashItem|null null if location was deleted, otherwise TrashItem
     */
    public function trash(Location $location): ?APITrashItem
    {
        if (empty($location->id)) {
            throw new InvalidArgumentValue('id', $location->id, 'Location');
        }

        if (!$this->userHasPermissionsToRemove($location->getContentInfo(), $location)) {
            throw new UnauthorizedException('content', 'remove');
        }

        $this->repository->beginTransaction();
        try {
            $spiTrashItem = $this->persistenceHandler->trashHandler()->trashSubtree($location->id);
            $this->persistenceHandler->urlAliasHandler()->locationDeleted($location->id);
            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        // Use internalLoadContent() as we want a trash item regardless of user access to the trash or not.
        try {
            return isset($spiTrashItem)
                ? $this->buildDomainTrashItemObject(
                    $spiTrashItem,
                    $this->repository->getContentService()->internalLoadContentById($spiTrashItem->contentId)
                )
                : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Recovers the $trashedLocation at its original place if possible.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     * @param \eZ\Publish\API\Repository\Values\Content\Location $newParentLocation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to recover the trash item at the parent location location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location the newly created or recovered location
     */
    public function recover(APITrashItem $trashItem, Location $newParentLocation = null): Location
    {
        if (!is_numeric($trashItem->id)) {
            throw new InvalidArgumentValue('id', $trashItem->id, 'TrashItem');
        }

        if ($newParentLocation === null && !is_numeric($trashItem->parentLocationId)) {
            throw new InvalidArgumentValue('parentLocationId', $trashItem->parentLocationId, 'TrashItem');
        }

        if ($newParentLocation !== null && !is_numeric($newParentLocation->id)) {
            throw new InvalidArgumentValue('parentLocationId', $newParentLocation->id, 'Location');
        }

        if (!$this->permissionResolver->canUser(
            'content',
            'restore',
            $trashItem->getContentInfo(),
            [$newParentLocation ?: $trashItem]
        )) {
            throw new UnauthorizedException('content', 'restore');
        }

        $this->repository->beginTransaction();
        try {
            $newParentLocationId = $newParentLocation ? $newParentLocation->id : $trashItem->parentLocationId;
            $newLocationId = $this->persistenceHandler->trashHandler()->recover(
                $trashItem->id,
                $newParentLocationId
            );

            $content = $this->repository->getContentService()->loadContent($trashItem->contentId);
            $urlAliasNames = $this->nameSchemaService->resolveUrlAliasSchema($content);

            // Publish URL aliases for recovered location
            foreach ($urlAliasNames as $languageCode => $name) {
                $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
                    $newLocationId,
                    $newParentLocationId,
                    $name,
                    $languageCode,
                    $content->contentInfo->alwaysAvailable
                );
            }

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }

        return $this->repository->getLocationService()->loadLocation($newLocationId);
    }

    /**
     * Empties trash.
     *
     * All locations contained in the trash will be removed. Content objects will be removed
     * if all locations of the content are gone.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to empty the trash
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList
     */
    public function emptyTrash(): TrashItemDeleteResultList
    {
        if ($this->permissionResolver->hasAccess('content', 'cleantrash') === false) {
            throw new UnauthorizedException('content', 'cleantrash');
        }

        $this->repository->beginTransaction();
        try {
            // Persistence layer takes care of deleting content objects
            $result = $this->persistenceHandler->trashHandler()->emptyTrash();
            $this->repository->commit();

            return $result;
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Deletes a trash item.
     *
     * The corresponding content object will be removed
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TrashItem $trashItem
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete this trash item
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult
     */
    public function deleteTrashItem(APITrashItem $trashItem): TrashItemDeleteResult
    {
        if (!$this->permissionResolver->canUser('content', 'cleantrash', $trashItem->getContentInfo())) {
            throw new UnauthorizedException('content', 'cleantrash');
        }

        if (!is_numeric($trashItem->id)) {
            throw new InvalidArgumentValue('id', $trashItem->id, 'TrashItem');
        }

        $this->repository->beginTransaction();
        try {
            $trashItemDeleteResult = $this->persistenceHandler->trashHandler()->deleteTrashItem($trashItem->id);
            $this->repository->commit();

            return $trashItemDeleteResult;
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Returns a collection of Trashed locations contained in the trash, which are readable by the current user.
     *
     * $query allows to filter/sort the elements to be contained in the collection.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Trash\SearchResult
     */
    public function findTrashItems(Query $query): SearchResult
    {
        if ($query->filter !== null && !$query->filter instanceof Criterion) {
            throw new InvalidArgumentValue('query->filter', $query->filter, 'Query');
        }

        if ($query->sortClauses !== null) {
            if (!is_array($query->sortClauses)) {
                throw new InvalidArgumentValue('query->sortClauses', $query->sortClauses, 'Query');
            }

            foreach ($query->sortClauses as $sortClause) {
                if (!$sortClause instanceof SortClause) {
                    throw new InvalidArgumentValue('query->sortClauses', 'only instances of the SortClause class are allowed');
                }
            }
        }

        if ($query->offset !== null && !is_numeric($query->offset)) {
            throw new InvalidArgumentValue('query->offset', $query->offset, 'Query');
        }

        if ($query->limit !== null && !is_numeric($query->limit)) {
            throw new InvalidArgumentValue('query->limit', $query->limit, 'Query');
        }

        $spiTrashResult = $this->persistenceHandler->trashHandler()->findTrashItems(
            $query->filter,
            $query->offset !== null && $query->offset > 0 ? (int)$query->offset : 0,
            $query->limit !== null && $query->limit >= 0 ? (int)$query->limit : null,
            $query->sortClauses
        );

        $trashItems = $this->buildDomainTrashItems($spiTrashResult->items);
        $searchResult = new SearchResult(['items' => $trashItems, 'totalCount' => $spiTrashResult->totalCount]);

        return $searchResult;
    }

    protected function buildDomainTrashItems(array $spiTrashItems): array
    {
        $trashItems = [];
        // TODO: load content in bulk once API allows for it
        foreach ($spiTrashItems as $spiTrashItem) {
            try {
                $trashItems[] = $this->buildDomainTrashItemObject(
                    $spiTrashItem,
                    $this->repository->getContentService()->loadContent($spiTrashItem->contentId)
                );
            } catch (APIUnauthorizedException $e) {
                // Do nothing, thus exclude items the current user doesn't have read access to.
            }
        }

        return $trashItems;
    }

    protected function buildDomainTrashItemObject(Trashed $spiTrashItem, Content $content): APITrashItem
    {
        return new TrashItem(
            [
                'content' => $content,
                'contentInfo' => $content->contentInfo,
                'id' => $spiTrashItem->id,
                'priority' => $spiTrashItem->priority,
                'hidden' => $spiTrashItem->hidden,
                'invisible' => $spiTrashItem->invisible,
                'remoteId' => $spiTrashItem->remoteId,
                'parentLocationId' => $spiTrashItem->parentId,
                'pathString' => $spiTrashItem->pathString,
                'depth' => $spiTrashItem->depth,
                'sortField' => $spiTrashItem->sortField,
                'sortOrder' => $spiTrashItem->sortOrder,
                'trashed' => isset($spiTrashItem->trashed) ? new DateTime('@' . $spiTrashItem->trashed) : new DateTime('@0'),
                'parentLocation' => $this->proxyDomainMapper->createLocationProxy($spiTrashItem->parentId),
            ]
        );
    }

    /**
     * @param int $timestamp
     *
     * @return \DateTime
     */
    protected function getDateTime($timestamp)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);

        return $dateTime;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return bool
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function userHasPermissionsToRemove(ContentInfo $contentInfo, Location $location)
    {
        if (!$this->permissionResolver->canUser('content', 'remove', $contentInfo, [$location])) {
            return false;
        }
        $contentRemoveCriterion = $this->permissionCriterionResolver->getPermissionsCriterion('content', 'remove');
        if (!$contentRemoveCriterion instanceof Criterion) {
            return (bool)$contentRemoveCriterion;
        }
        $query = new Query(
            [
                'limit' => 0,
                'filter' => new CriterionLogicalAnd(
                    [
                        new CriterionSubtree($location->pathString),
                        new CriterionLogicalNot($contentRemoveCriterion),
                    ]
                ),
            ]
        );
        $result = $this->repository->getSearchService()->findContent($query, [], false);

        return $result->totalCount == 0;
    }
}
