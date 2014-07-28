<?php
/**
 * File containing the Aggregate criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
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
     * @var \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor[]
     */
    protected $visitors = array();

    /**
     * Construct from optional visitor array
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor[] $visitors
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
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor $visitor
     */
    public function addVisitor( CriterionVisitor $visitor )
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Check if visitor is applicable to current criterion
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return true;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor $subVisitor
     *
     * @return string
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

        throw new NotImplementedException(
            "No visitor available for: " . get_class( $criterion ) . ' with operator ' . $criterion->operator
        );
    }
}
