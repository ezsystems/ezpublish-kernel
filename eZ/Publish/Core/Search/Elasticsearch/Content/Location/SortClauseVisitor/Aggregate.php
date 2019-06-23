<?php

/**
 * File containing the SortClauseVisitor\Aggregate class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\Location\SortClauseVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;

/**
 * Aggregate sort clause visitor.
 */
class Aggregate extends SortClauseVisitor
{
    /**
     * Array of available visitors.
     *
     * @var \eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor[]
     */
    protected $visitors = [];

    /**
     * Construct from optional visitor array.
     *
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor[] $visitors
     */
    public function __construct(array $visitors = [])
    {
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    /**
     * Adds visitor.
     *
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\SortClauseVisitor $visitor
     */
    public function addVisitor(SortClauseVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * Check if visitor is applicable to current SortClause.
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
     * Map field value to a proper Elasticsearch representation.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return mixed
     */
    public function visit(SortClause $sortClause)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($sortClause)) {
                return $visitor->visit($sortClause, $this);
            }
        }

        throw new NotImplementedException(
            'No visitor available for: ' . get_class($sortClause)
        );
    }
}
