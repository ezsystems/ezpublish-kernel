<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Subtree class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use InvalidArgumentException;

/**
 * Criterion that matches content that belongs to a given (list of) Subtree(s).
 *
 * Content will be matched if it is part of at least one of the given subtree path strings
 */
class Subtree extends Criterion
{
    /**
     * Creates a new SubTree criterion.
     *
     * @param string|string[] $value an array of subtree path strings, eg: /1/2/
     *
     * @throws InvalidArgumentException if a non path string is given
     * @throws InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct($value)
    {
        foreach ((array)$value as $pathString) {
            if (preg_match('/^(\/\w+)+\/$/', $pathString) !== 1) {
                throw new InvalidArgumentException("value '$pathString' must follow the pathString format, eg /1/2/");
            }
        }

        parent::__construct(null, null, $value);
    }

    public function getSpecifications()
    {
        return [
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_STRING
            ),
        ];
    }

    /**
     * @deprecated since 7.2, will be removed in 8.0. Use the constructor directly instead.
     */
    public static function createFromQueryBuilder($target, $operator, $value)
    {
        @trigger_error('The ' . __METHOD__ . ' method is deprecated since version 7.2 and will be removed in 8.0.', E_USER_DEPRECATED);

        return new self($value);
    }
}
