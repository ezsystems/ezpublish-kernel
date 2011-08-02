<?php
/**
 * File containing the ezp\Persistence\Content\Interfaces\Criterion interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Interfaces;

/**
 * Base interface for Criterion implementations
 */
interface Criterion
{
    /**
     * Constructs a Criterion for $target with operator $operator on $value
     *
     * @param string $target The target (field identifier for a field, metadata identifier, etc)
     * @param string $operator The criterion operator, from Criterion\Operator
     * @param mixed $value The Criterion value, either as an individual item or an array
     */
    public function __construct( $target, $operator, $value );

    /**
     * Criterion description function.
     *
     * Returns the combination of the Criterion's supported operator/value,
     * as an array of Criterion\OperatorSpecifications objects
     * - Operator is one supported Operator, as an Operator::* constant
     * - ValueType is the type of input value this operator requires, either array or single
     * - SupportedTypes is an array of types the operator will accept
     * - ValueCountLimitation is an integer saying how many values are expected.
     *
     * <code>
     * // IN and EQ are supported
     * return array(
     *     // The EQ operator expects a single value, either as an integer or a string
     *     new OperatorSpecifications(
     *         Operator::EQ,
     *         OperatorSpecifications::INPUT_TYPE_SINGLE,
     *         array( OperatorSpecifications::INPUT_VALUE_INTEGER, OperatorSpecifications::INPUT_VALUE_STRING ),
     *     ),
     *     // The IN operator expects an array of values, of either integers or strings
     *     new OperatorSpecifications(
     *         Operator::IN,
     *         OperatorSpecifications::INPUT_TYPE_ARRAY,
     *         array( OperatorSpecifications::INPUT_VALUE_INTEGER, OperatorSpecifications::INPUT_VALUE_STRING )
     *     )
     * )*
     * </code>
     * @return \ezp\Persistence\Content\Criterion\OperatorSpecifications[]
     */
    public function getSpecifications();
}
?>
