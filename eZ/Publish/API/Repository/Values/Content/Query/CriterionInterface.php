<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */

namespace eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Base interface for Criterion implementations
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */
interface CriterionInterface
{
    /**
     * Creates a new Criterion for $target with operator $operator on $value
     *
     * @param string $target The target (field identifier for a field, metadata identifier, etc)
     * @param string $operator The criterion operator, from Criterion\Operator
     * @param mixed $value The Criterion value, either as an individual item or an array
     *
     *@return CriterionInterface
     */
    public static function createFromQueryBuilder( $target, $operator, $value );

    /**
     * Criterion description function.
     *
     * Returns the combination of the Criterion's supported operator/value,
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
     * @return \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications[]
     */
    public function getSpecifications();
}
