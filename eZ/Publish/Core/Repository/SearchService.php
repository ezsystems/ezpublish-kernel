<?php

/**
 * File containing the eZ\Publish\Core\Repository\SearchService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location as LocationCriterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\Location as LocationSortClause;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\Search\Handler;

/**
 * Search service.
 */
class SearchService implements SearchServiceInterface
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Search\Handler
     */
    protected $searchHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \eZ\Publish\Core\Repository\Helper\DomainMapper
     */
    protected $domainMapper;

    /**
     * @var \eZ\Publish\Core\Repository\PermissionsCriterionHandler
     */
    protected $permissionsCriterionHandler;

    /**
     * Setups service with reference to repository object that created it & corresponding handler.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Search\Handler $searchHandler
     * @param \eZ\Publish\Core\Repository\Helper\DomainMapper $domainMapper
     * @param \eZ\Publish\Core\Repository\PermissionsCriterionHandler $permissionsCriterionHandler
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        Handler $searchHandler,
        Helper\DomainMapper $domainMapper,
        PermissionsCriterionHandler $permissionsCriterionHandler,
        array $settings = array()
    ) {
        $this->repository = $repository;
        $this->searchHandler = $searchHandler;
        $this->domainMapper = $domainMapper;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            //'defaultSetting' => array(),
        );
        $this->permissionsCriterionHandler = $permissionsCriterionHandler;
    }

    /**
     * Finds content objects for the given query.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if query is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $languageFilter Configuration for specifying prioritized languages query will be performed on.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations.
     * @param bool $filterOnUserPermissions if true only the objects which the user is allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent(Query $query, array $languageFilter = array(), $filterOnUserPermissions = true)
    {
        $contentService = $this->repository->getContentService();
        $result = $this->internalFindContentInfo($query, $languageFilter, $filterOnUserPermissions);
        foreach ($result->searchHits as $hit) {
            // As we get ContentInfo from SPI, we need to load full content (avoids getting stale content data)
            $hit->valueObject = $contentService->internalLoadContent(
                $hit->valueObject->id,
                (!empty($languageFilter['languages']) ? $languageFilter['languages'] : null),
                null,
                false,
                (isset($languageFilter['useAlwaysAvailable']) ? $languageFilter['useAlwaysAvailable'] : true)
            );
        }

        return $result;
    }

    /**
     * Finds contentInfo objects for the given query.
     *
     * @see SearchServiceInterface::findContentInfo()
     *
     * @since 5.4.5
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if query is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $languageFilter - a map of filters for the returned fields.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations.
     * @param bool $filterOnUserPermissions if true (default) only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContentInfo(Query $query, array $languageFilter = array(), $filterOnUserPermissions = true)
    {
        $result = $this->internalFindContentInfo($query, $languageFilter, $filterOnUserPermissions);
        foreach ($result->searchHits as $hit) {
            $hit->valueObject = $this->domainMapper->buildContentInfoDomainObject(
                $hit->valueObject
            );
        }

        return $result;
    }

    /**
     * Finds SPI content info objects for the given query.
     *
     * Internal for use by {@link findContent} and {@link findContentInfo}.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if query is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $languageFilter - a map of filters for the returned fields.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations.
     * @param bool $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult With "raw" SPI contentInfo objects in result
     */
    protected function internalFindContentInfo(Query $query, array $languageFilter = array(), $filterOnUserPermissions = true)
    {
        if (!is_int($query->offset)) {
            throw new InvalidArgumentType(
                '$query->offset',
                'integer',
                $query->offset
            );
        }

        if (!is_int($query->limit)) {
            throw new InvalidArgumentType(
                '$query->limit',
                'integer',
                $query->limit
            );
        }

        $query = clone $query;
        $query->filter = $query->filter ?: new Criterion\MatchAll();

        $this->validateContentCriteria(array($query->query), '$query');
        $this->validateContentCriteria(array($query->filter), '$query');
        $this->validateContentSortClauses($query);

        if ($filterOnUserPermissions && !$this->permissionsCriterionHandler->addPermissionsCriterion($query->filter)) {
            return new SearchResult(array('time' => 0, 'totalCount' => 0));
        }

        return $this->searchHandler->findContent($query, $languageFilter);
    }

    /**
     * Checks that $criteria does not contain Location criterions.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion[] $criteria
     * @param string $argumentName
     */
    protected function validateContentCriteria(array $criteria, $argumentName)
    {
        foreach ($criteria as $criterion) {
            if ($criterion instanceof LocationCriterion) {
                throw new InvalidArgumentException(
                    $argumentName,
                    'Location criterions cannot be used in Content search'
                );
            }
            if ($criterion instanceof LogicalOperator) {
                $this->validateContentCriteria($criterion->criteria, $argumentName);
            }
        }
    }

    /**
     * Checks that $query does not contain Location sort clauses.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     */
    protected function validateContentSortClauses(Query $query)
    {
        foreach ($query->sortClauses as $sortClause) {
            if ($sortClause instanceof LocationSortClause) {
                throw new InvalidArgumentException('$query', 'Location sort clauses cannot be used in Content search');
            }
        }
    }

    /**
     * Performs a query for a single content object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if criterion is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than one result matching the criterions
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $languageFilter Configuration for specifying prioritized languages query will be performed on.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations.
     * @param bool $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function findSingle(Criterion $filter, array $languageFilter = array(), $filterOnUserPermissions = true)
    {
        $this->validateContentCriteria(array($filter), '$filter');

        if ($filterOnUserPermissions && !$this->permissionsCriterionHandler->addPermissionsCriterion($filter)) {
            throw new NotFoundException('Content', '*');
        }

        $contentInfo = $this->searchHandler->findSingle($filter, $languageFilter);

        return $this->repository->getContentService()->internalLoadContent(
            $contentInfo->id,
            (!empty($languageFilter['languages']) ? $languageFilter['languages'] : null),
            null,
            false,
            (isset($languageFilter['useAlwaysAvailable']) ? $languageFilter['useAlwaysAvailable'] : true)
        );
    }

    /**
     * Suggests a list of values for the given prefix.
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest($prefix, $fieldPaths = array(), $limit = 10, Criterion $filter = null)
    {
    }

    /**
     * Finds Locations for the given query.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if query is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $languageFilter Configuration for specifying prioritized languages query will be performed on.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations
     * @param bool $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations(LocationQuery $query, array $languageFilter = array(), $filterOnUserPermissions = true)
    {
        if (!is_int($query->offset)) {
            throw new InvalidArgumentType(
                '$query->offset',
                'integer',
                $query->offset
            );
        }

        if (!is_int($query->limit)) {
            throw new InvalidArgumentType(
                '$query->limit',
                'integer',
                $query->limit
            );
        }

        $query = clone $query;
        $query->filter = $query->filter ?: new Criterion\MatchAll();

        if ($filterOnUserPermissions && !$this->permissionsCriterionHandler->addPermissionsCriterion($query->filter)) {
            return new SearchResult(array('time' => 0, 'totalCount' => 0));
        }

        $result = $this->searchHandler->findLocations($query, $languageFilter);

        foreach ($result->searchHits as $hit) {
            $hit->valueObject = $this->domainMapper->buildLocationDomainObject(
                $hit->valueObject
            );
        }

        return $result;
    }
}
