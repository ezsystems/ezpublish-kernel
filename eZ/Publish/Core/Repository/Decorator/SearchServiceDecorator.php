<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

abstract class SearchServiceDecorator implements SearchService
{
    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\SearchService $service
     */
    public function __construct(SearchService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function findContent(Query $query, array $languageFilter = [], $filterOnUserPermissions = true)
    {
        return $this->service->findContent($query, $languageFilter, $filterOnUserPermissions);
    }

    /**
     * {@inheritdoc}
     */
    public function findContentInfo(Query $query, array $languageFilter = [], $filterOnUserPermissions = true)
    {
        return $this->service->findContentInfo($query, $languageFilter, $filterOnUserPermissions);
    }

    /**
     * {@inheritdoc}
     */
    public function findSingle(Criterion $filter, array $languageFilter = [], $filterOnUserPermissions = true)
    {
        return $this->service->findSingle($filter, $languageFilter, $filterOnUserPermissions);
    }

    /**
     * {@inheritdoc}
     */
    public function suggest($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null)
    {
        return $this->service->suggest($prefix, $fieldPaths, $limit, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function findLocations(LocationQuery $query, array $languageFilter = [], $filterOnUserPermissions = true)
    {
        return $this->service->findLocations($query, $languageFilter, $filterOnUserPermissions);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($capabilityFlag)
    {
        return $this->service->supports($capabilityFlag);
    }
}
