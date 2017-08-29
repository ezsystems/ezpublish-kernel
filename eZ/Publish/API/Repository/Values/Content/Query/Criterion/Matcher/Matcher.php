<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\Matcher;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CriterionInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use InvalidArgumentException;

abstract class Matcher implements CriterionInterface
{
    /**
     * The operator used by the Criterion.
     *
     * @var string
     */
    public $operator;

    /**
     * The value(s) matched by the criteria.
     *
     * @var array(int|string)
     */
    public $value;

    /**
     * The target used by the criteria (field, metadata...).
     *
     * @var string
     */
    public $target;

    /**
     * Additional value data, required by some criterions, MapLocationDistance for instance.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value
     */
    public $valueData;

    /**
     * Performs operator validation based on the Criterion specifications returned by {@see getSpecifications()}.
     *
     * @param string|null $target The target the Criterion applies to: metadata identifier, field identifier...
     * @param string|null $operator
     *        The operator the Criterion uses. If null is given, will default to Operator::IN if $value is an array,
     *        Operator::EQ if it is not.
     * @param string[]|int[]|int|string $value
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Value $valueData
     *
     * @todo Add a dedicated exception
     *
     * @throws \InvalidArgumentException if the provided operator isn't supported
     */
    public function __construct($target, $operator, $value, Value $valueData = null)
    {
        if ($operator === null) {
            $operator = is_array($value) ? Operator::IN : Operator::EQ;
        }

        $operatorFound = false;

        // we loop on each specified operator.
        // If the provided operator ain't found, an exception will be thrown at the end
        foreach ($this->getSpecifications() as $operatorSpecifications) {
            if ($operatorSpecifications->operator != $operator) {
                continue;
            }
            $operatorFound = true;

            // input format check (single/array)
            switch ($operatorSpecifications->valueFormat) {
                case Specifications::FORMAT_SINGLE:
                    if (is_array($value)) {
                        throw new InvalidArgumentException('The Criterion expects a single value');
                    }
                    break;

                case Specifications::FORMAT_ARRAY:
                    if (!is_array($value)) {
                        throw new InvalidArgumentException('The criterion expects an array of values');
                    }
                    break;
            }

            // input value check
            if ($operatorSpecifications->valueTypes !== null) {
                $callback = $this->getValueTypeCheckCallback($operatorSpecifications->valueTypes);
                if (!is_array($value)) {
                    $value = array($value);
                }
                foreach ($value as $item) {
                    if ($callback($item) === false) {
                        throw new InvalidArgumentException('Unsupported value (' . gettype($item) . ")$item");
                    }
                }
            }
        }

        // Operator wasn't found in the criterion specifications
        if ($operatorFound === false) {
            throw new InvalidArgumentException("Operator $operator isn't supported by the Criterion " . get_class($this));
        }

        $this->operator = $operator;
        $this->value = $value;
        $this->target = $target;
        $this->valueData = $valueData;
    }

    /**
     * Matcher description function.
     *
     * Returns the combination of the Matcher's supported operator/value,
     * as an array of eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications objects
     * - Operator is one supported Operator, as an Operator::* constant
     * - ValueType is the type of input value this operator requires, either array or single
     * - SupportedTypes is an array of types the operator will accept
     * - ValueCountLimitation is an integer saying how many values are expected.
     *
     * <code>
     * // IN and EQ are supported
     * return array(
     *     // The EQ operator expects a single value, either as an integer or a string
     *     new Specifications(
     *         Operator::EQ,
     *         Specifications::INPUT_TYPE_SINGLE,
     *         array( Specifications::INPUT_VALUE_INTEGER, Specifications::INPUT_VALUE_STRING ),
     *     ),
     *     // The IN operator expects an array of values, of either integers or strings
     *     new Specifications(
     *         Operator::IN,
     *         Specifications::INPUT_TYPE_ARRAY,
     *         array( Specifications::INPUT_VALUE_INTEGER, Specifications::INPUT_VALUE_STRING )
     *     )
     * )*
     * </code>
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications[]
     */
    abstract public function getSpecifications();

    /**
     * Returns a callback that checks the values types depending on the operator specifications.
     *
     * @param int $valueTypes The accepted values, as a bit field of Specifications::TYPE_* constants
     *
     * @return \Closure
     */
    private function getValueTypeCheckCallback($valueTypes)
    {
        $callback = function ($value) {
            return false;
        };

        // the callback code will return true as soon as an accepted value type is found
        if ($valueTypes & Specifications::TYPE_INTEGER) {
            $callback = function ($value) use ($callback) {
                return is_numeric($value) || $callback($value);
            };
        }
        if ($valueTypes & Specifications::TYPE_STRING) {
            $callback = function ($value) use ($callback) {
                return is_string($value) || $callback($value);
            };
        }
        if ($valueTypes & Specifications::TYPE_BOOLEAN) {
            $callback = function ($value) use ($callback) {
                return is_bool($value) || $callback($value);
            };
        }

        return $callback;
    }

    /**
     * Creates a new Matcher for $target with operator $operator on $value.
     *
     * @param string $target The target (field identifier for a field, metadata identifier, etc)
     * @param string $operator The matcher operator, from Criterion\Operator
     * @param mixed $value The Matcher value, either as an individual item or an array
     *
     * @return Matcher
     */
    public static function createFromQueryBuilder($target, $operator, $value)
    {
        return new static($target, $operator, $value);
    }
}
