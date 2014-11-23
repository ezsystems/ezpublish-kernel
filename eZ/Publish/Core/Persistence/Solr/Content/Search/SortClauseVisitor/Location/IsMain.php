<?php
/**
 * File containing the Solr Visitor for Location SortClause
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor\Location;

use eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits SortClause\Location\IsMainLocation
 */
class IsMain extends SortClauseVisitor
{
    /**
     * CHeck if visitor is applicable to current sortClause
     *
     * @param SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return $sortClause instanceof SortClause\Location\IsMainLocation;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param SortClause $sortClause
     * @param bool $isChildQuery
     *
     * @return string
     */
    public function visit( SortClause $sortClause, $isChildQuery = false )
    {
        return 'is_main_b' . $this->getDirection( $sortClause );
    }
}

