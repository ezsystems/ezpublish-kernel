<?php
/**
 * File containing the Location\IsMainLocation sort clause visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\SortClauseVisitor\Location;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the Location\IsMainLocation sort clause
 */
class IsMainLocation extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current SortClause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\Location\IsMainLocation;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return mixed
     */
    public function visit( SortClause $sortClause )
    {
        return array(
            "is_main_location_b" => array(
                "order" => $this->getDirection( $sortClause ),
            ),
        );
    }
}
