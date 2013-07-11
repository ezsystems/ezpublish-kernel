<?php
/**
 * File containing the SortClauseVisitor\SectionName class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

/**
 * Visits the sortClause tree into a Solr query
 */
class SectionName extends SortClauseVisitor
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
        return $sortClause instanceof SortClause\SectionName;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param SortClause $sortClause
     *
     * @return void
     */
    public function visit( SortClause $sortClause )
    {
        return 'section_name_s' . $this->getDirection( $sortClause );
    }
}

