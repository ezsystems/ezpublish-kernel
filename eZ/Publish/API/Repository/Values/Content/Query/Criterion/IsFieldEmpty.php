<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\IsFieldEmpty class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use InvalidArgumentException;

/**
 * A criterion that matches content based on if Field is empty.
 */
class IsFieldEmpty extends Criterion
{
    /**
     * Empty constant: empty.
     */
    public const EMPTY = 1;

    /**
     * Empty constant: not empty.
     */
    public const NOT_EMPTY = 0;

    /**
     * @param string $fieldDefinitionIdentifier
     * @param mixed $value Field content: self::IS_EMPTY, self::IS_NOT_EMPTY
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($fieldDefinitionIdentifier, $value)
    {
        if ($value !== self::EMPTY && $value !== self::NOT_EMPTY) {
            throw new InvalidArgumentException("Invalid IsFieldEmpty value {$value}");
        }

        parent::__construct($fieldDefinitionIdentifier, null, $value);
    }

    public function getSpecifications()
    {
        return [
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER),
        ];
    }
}
