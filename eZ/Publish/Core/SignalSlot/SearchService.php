<?php

/**
 * SearchService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * SearchService class.
 */
class SearchService implements SearchServiceInterface
{
    /**
     * Aggregated service.
     *
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $service;

    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\SearchService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(SearchServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        $this->service = $service;
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Finds content objects for the given query.
     *
     * @todo define structs for the field filters
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if query is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param bool $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findContent(Query $query, array $fieldFilters = array(), $filterOnUserPermissions = true)
    {
        return $this->service->findContent($query, $fieldFilters, $filterOnUserPermissions);
    }

    /**
     * Performs a query for a single content object.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if criterion is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is more than than one result matching the criterions
     *
     * @todo define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $filter
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param bool $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function findSingle(Criterion $filter, array $fieldFilters = array(), $filterOnUserPermissions = true)
    {
        return $this->service->findSingle($filter, $fieldFilters, $filterOnUserPermissions);
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
        return $this->service->suggest($prefix, $fieldPaths, $limit, $filter);
    }

    /**
     * Finds Locations for the given query.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if query is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supports: <code>array("languages" => array(<language1>,..), "useAlwaysAvailable" => bool)</code>
     *                            useAlwaysAvailable defaults to true to avoid exceptions on missing translations
     * @param bool $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult
     */
    public function findLocations(LocationQuery $query, array $fieldFilters = array(), $filterOnUserPermissions = true)
    {
        return $this->service->findLocations($query, $fieldFilters, $filterOnUserPermissions);
    }
}
