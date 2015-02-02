<?php
/**
 * File containing the SortClauseVisitor\Aggregate class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Visits the sortClause tree into a Solr query
 */
class Aggregate extends SortClauseVisitor
{
    /**
     * Array of available visitors
     *
     * @var array
     */
    protected $visitors = array();

    /**
     * COnstruct from optional visitor array
     *
     * @param array $visitors
     *
     * @return void
     */
    public function __construct( array $visitors = array() )
    {
        foreach ( $visitors as $visitor )
        {
            $this->addVisitor( $visitor );
        }
    }

    /**
     * Adds visitor
     *
     * @param FieldValueVisitor $visitor
     *
     * @return void
     */
    public function addVisitor( SortClauseVisitor $visitor )
    {
        $this->visitors[] = $visitor;
    }

    /**
     * CHeck if visitor is applicable to current sortClause
     *
     * @param SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return true;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param SortClause $sortClause
     *
     * @return string
     */
    public function visit( SortClause $sortClause )
    {
        foreach ( $this->visitors as $visitor )
        {
            if ( $visitor->canVisit( $sortClause ) )
            {
                return $visitor->visit( $sortClause, $this );
            }
        }

        throw new NotImplementedException( "No visitor available for: " . get_class( $sortClause ) );
    }
}

