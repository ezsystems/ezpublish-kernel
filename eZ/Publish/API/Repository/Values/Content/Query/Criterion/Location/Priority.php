<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Priority class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;

/**
 * A criterion that matches Location based on its priority.
 *
 * Supported operators:
 * - BETWEEN: matches location whose priority is between (included) the TWO given priorities
 * - GT, GTE: matches location whose priority is greater than/greater than or equals the given priority
 * - LT, LTE: matches location whose priority is lower than/lower than or equals the given priority
 */
class Priority extends Location
{
    /**
     * Creates a new LocationPriority criterion.
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
            new Specifications(Operator::BETWEEN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER),
            new Specifications(Operator::GT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::GTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::LT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
            new Specifications(Operator::LTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
        ];
    }

    /**
     * @deprecated since 7.2, will be removed in 8.0. Use the constructor directly instead.
     */
    public static function createFromQueryBuilder($target, $operator, $value)
    {
        @trigger_error('The ' . __METHOD__ . ' method is deprecated since version 7.2 and will be removed in 8.0.', E_USER_DEPRECATED);

        return new self($operator, $value);
    }
}
