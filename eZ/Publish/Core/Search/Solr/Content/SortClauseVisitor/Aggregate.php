<?php

/**
 * File containing the SortClauseVisitor\Aggregate class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor;

use eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Visits the sortClause tree into a Solr query.
 */
class Aggregate extends SortClauseVisitor
{
    /**
     * Array of available visitors.
     *
     * @var \eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor[]
     */
    protected $visitors = array();

    /**
     * Construct from optional visitor array.
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor[] $visitors
     */
    public function __construct(array $visitors = array())
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds visitor.
     *
     * @param \eZ\Publish\Core\Search\Solr\Content\SortClauseVisitor $visitor
     */
    public function addVisitor(SortClauseVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Checks if visitor is applicable to current sortClause.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return true;
    }

    /**
     * Maps sort clause to a proper Solr representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException If visitor is not found
     *
     * @param SortClause $sortClause
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($sortClause)) {
                return $visitor->visit($sortClause, $this);
            }
        }

        throw new NotImplementedException('No visitor available for: ' . get_class($sortClause));
    }
}
