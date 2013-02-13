<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Visits the criterion tree into a Solr query
 */
class Aggregate extends CriterionVisitor
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
    public function addVisitor( CriterionVisitor $visitor )
    {
        $this->visitors[] = $visitor;
    }

    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return true;
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return void
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        foreach ( $this->visitors as $visitor )
        {
            if ( $visitor->canVisit( $criterion ) )
            {
                return $visitor->visit( $criterion, $this );
            }
        }

        throw new NotImplementedException( "No visitor available for: " . get_class( $criterion ) . ' with operator ' . $criterion->operator );
    }
}

