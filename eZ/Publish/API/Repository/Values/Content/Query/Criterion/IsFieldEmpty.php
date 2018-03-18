<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use InvalidArgumentException;

/**
 * IsFieldEmpty criterion matches Content field based on if its value is empty or not.
 */
class IsFieldEmpty extends Criterion implements CriterionInterface
{
    /**
     * Indicates that the field should be empty.
     *
     * @var mixed
     */
    const IS_EMPTY = 0;

    /**
     * Indicates that the field should not be empty.
     *
     * @var mixed
     */
    const IS_NOT_EMPTY = 1;

    /**
     * @param string $fieldDefinitionIdentifier
     * @param mixed $value Field content: self::IS_EMPTY, self::IS_NOT_EMPTY
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($fieldDefinitionIdentifier, $value)
    {
        if ($value !== self::IS_EMPTY && $value !== self::IS_NOT_EMPTY) {
            throw new InvalidArgumentException("Invalid field empty status value {$value}");
        }

        parent::__construct($fieldDefinitionIdentifier, null, $value);
    }

    public function getSpecifications()
    {
        return [
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
        ];
    }

    public static function createFromQueryBuilder($target, $operator, $value)
    {
        return new self($target, $value);
    }
}
