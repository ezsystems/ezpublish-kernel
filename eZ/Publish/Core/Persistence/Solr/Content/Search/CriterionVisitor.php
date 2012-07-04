<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the criterion tree into a Solr query
 */
abstract class CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     * @return bool
     */
    abstract public function canVisit( Criterion $criterion );

    /**
     * Map field value to a proper Solr representation
     *
     * @param DocumentField $field
     * @return void
     */
    abstract public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null );
}

