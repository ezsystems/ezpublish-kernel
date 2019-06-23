<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Depth class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * The Depth Criterion class.
 *
 * Provides Location filtering based on depth
 */
class Depth extends Location
{
    /**
     * Creates a new Depth criterion.
     *
     * @throws \InvalidArgumentException if a non numeric id is given
     * @throws \InvalidArgumentException if the value type doesn't match the operator
     *
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator
     */
    public function __construct($operator, $value)
    {
        parent::__construct(null, $operator, $value);
    }

    public function getSpecifications()
    {
        return [
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::GT,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::GTE,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::LT,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::LTE,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::BETWEEN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER,
                2
            ),
        ];
    }
}
