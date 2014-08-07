<?php
/**
 * File containing the SortClauseVisitor\Aggregate class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\Location\SortClauseVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Visits the sortClause tree into an Elasticsearch query
 */
class Aggregate extends SortClauseVisitor
{
    /**
     * Array of available visitors
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor[]
     */
    protected $visitors = array();

    /**
     * Construct from optional visitor array
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor[] $visitors
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
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\SortClauseVisitor $visitor
     *
     * @return void
     */
    public function addVisitor( SortClauseVisitor $visitor )
    {
        $this->visitors[] = $visitor;
    }

    /**
     * CHeck if visitor is applicable to current SortClause
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return boolean
     */
    public function canVisit( SortClause $sortClause )
    {
        return true;
    }

    /**
     * Map field value to a proper Elasticsearch representation
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
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

        throw new NotImplementedException(
            "No visitor available for: " . get_class( $sortClause )
        );
    }
}

