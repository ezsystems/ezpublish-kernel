<?php
/**
 * File containing the DatePublished sort clause class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\SortClauseVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the DatePublished sort clause
 */
class DatePublished extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sort clause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\DatePublished;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
     */
    public function visit( SortClause $sortClause )
    {
        return array(
            "content_published_dt" => array(
                "order" => $this->getDirection( $sortClause ),
            ),
        );
    }
}
