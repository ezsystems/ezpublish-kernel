<?php

/**
 * File containing the SectionName sort clause visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Location\SortClauseVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the SectionName sort clause.
 */
class SectionName extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current SortClause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\SectionName;
    }

    /**
     * Map field value to a proper Elasticsearch representation.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return mixed
     */
    public function visit(SortClause $sortClause)
    {
        return [
            'content_section_name_s' => [
                'order' => $this->getDirection($sortClause),
            ],
        ];
    }
}
