<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalNot class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Matcher;

/**
 * A NOT logical criterion.
 */
class LogicalNot extends LogicalOperator
{
    /**
     * Creates a new NOT logic criterion.
     *
     * Will match of the given criterion doesn't match
     *
     * @param Matcher[] $criteria One criterion, as an array
     *
     * @throws \InvalidArgumentException if more than one criterion is given in the array parameter
     */
    public function __construct(Matcher $criterion)
    {
        parent::__construct(array($criterion));
    }
}
