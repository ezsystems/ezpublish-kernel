<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

abstract class SearchServiceDecorator implements SearchService
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    protected $innerService;

    public function __construct(SearchService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function findContent(
        Query $query,
        array $languageFilter = [],
        $filterOnUserPermissions = true
    ) {
        return $this->innerService->findContent($query, $languageFilter, $filterOnUserPermissions);
    }

    public function findContentInfo(
        Query $query,
        array $languageFilter = [],
        $filterOnUserPermissions = true
    ) {
        return $this->innerService->findContentInfo($query, $languageFilter, $filterOnUserPermissions);
    }

    public function findSingle(
        Criterion $filter,
        array $languageFilter = [],
        $filterOnUserPermissions = true
    ) {
        return $this->innerService->findSingle($filter, $languageFilter, $filterOnUserPermissions);
    }

    public function suggest(
        $prefix,
        $fieldPaths = [],
        $limit = 10,
        Criterion $filter = null
    ) {
        return $this->innerService->suggest($prefix, $fieldPaths, $limit, $filter);
    }

    public function findLocations(
        LocationQuery $query,
        array $languageFilter = [],
        $filterOnUserPermissions = true
    ) {
        return $this->innerService->findLocations($query, $languageFilter, $filterOnUserPermissions);
    }

    public function supports($capabilityFlag)
    {
        return $this->innerService->supports($capabilityFlag);
    }
}
