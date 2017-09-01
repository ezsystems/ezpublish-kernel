<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator\LogicalNot class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CriterionInterface;

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
     * @param CriterionInterface $criterion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(CriterionInterface $criterion)
    {
        parent::__construct(array($criterion));
    }
}
