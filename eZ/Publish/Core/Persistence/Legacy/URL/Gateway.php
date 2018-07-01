<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\URL;

use eZ\Publish\API\Repository\Values\URL\Query\Criterion;
use eZ\Publish\API\Repository\Values\URL\Query\SortClause;
use eZ\Publish\SPI\Persistence\URL\URL;

abstract class Gateway
{
    /**
     * Update the URL.
     *
     * @param \eZ\Publish\SPI\Persistence\URL\URL $url
     */
    abstract public function updateUrl(URL $url);

    /**
     * Selects URLs matching specified criteria.
     *
     * @param \eZ\Publish\API\Repository\Values\URL\Query\Criterion $criterion
     * @param int $offset
     * @param int $limit
     * @param SortClause[] $sortClauses
     * @param bool $doCount
     * @return array
     */
    abstract public function find(Criterion $criterion, $offset, $limit, array $sortClauses = [], $doCount = true);

    /**
     * Returns IDs of Content Objects using URL identified by $id.
     *
     * @param int $id
     * @return array
     */
    abstract public function findUsages($id);

    /**
     * Loads URL with url id.
     *
     * @param int $id
     * @return array
     */
    abstract public function loadUrlData($id);

    /**
     * Loads URL with url address.
     *
     * @param int $url
     * @return array
     */
    abstract public function loadUrlDataByUrl($url);
}
