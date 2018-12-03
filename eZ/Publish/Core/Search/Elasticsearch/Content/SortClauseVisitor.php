<?php

/**
 * File containing the base SortClauseVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content;

use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the sort clause into a hash representation of Elasticsearch sort.
 *
 * @deprecated
 */
abstract class SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    abstract public function canVisit(SortClause $sortClause);

    /**
     * Map field value to a proper Elasticsearch representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return mixed Hash representation of Elasticsearch sort
     */
    abstract public function visit(SortClause $sortClause);

    /**
     * Get Elasticsearch sort direction for sort clause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
     */
    protected function getDirection(SortClause $sortClause)
    {
        return $sortClause->direction === 'descending' ? 'desc' : 'asc';
    }
}
