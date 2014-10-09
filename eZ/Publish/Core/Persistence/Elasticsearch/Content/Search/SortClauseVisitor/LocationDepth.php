<?php
/**
 * File containing the LocationDepth sort clause visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the LocationDepth sort clause
 */
class LocationDepth extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sortClause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\LocationDepth;
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
            "locations_doc.depth_i" => array(
                "mode" => "max",
                "order" => $this->getDirection( $sortClause ),
                "nested_filter" => array(
                    "term" => array(
                        "locations_doc.is_main_location_b" => true,
                    ),
                ),
            ),
        );
    }
}
