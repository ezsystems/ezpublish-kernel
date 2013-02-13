<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * This class is used by Criteria to describe which operators they support
 *
 * Instances of this class are returned in an array by the {@see Criterion::getSpecifications()} method
 * @package eZ\Publish\API\Repository\Values\Content\Query
 */
class Specifications
{
    /**
     * Criterion input type description constants.
     */
    const FORMAT_SINGLE = 'single';
    const FORMAT_ARRAY = 'array';

    /**
     * Criterion input value type description constants.
     * Used by {@see getDescription()} to say which type of values an operator expects
     */
    const TYPE_INTEGER = 1;
    const TYPE_STRING = 2;
    const TYPE_BOOLEAN = 4;

    /**
     * Specified operator, as one of the Operator::* constants
     */
    public $operator;

    /**
     * Format supported for the Criterion value, either single (INPUT_TYPE_SINGLE) or multiple (INPUT_TYPE_ARRAY)
     *
     * @see self::INPUT_TYPE_*
     * @param self::INPUT_VALUE_*
     */
    public $valueFormat;

    /**
     * Accepted values types, specifying what type of variables are accepted as a value
     *
     * @see self::INPUT_VALUE_*
     * @var int
     */
    public $valueTypes;

    /**
     * Limitation on the number of items as the value
     *
     * Only usable if {@see $parameterInputType} is {@see self::INPUT_TYPE_ARRAY}.
     * Not setting it means that 1...n will be required
     * @var int
     */
    public $valueCount;

    /**
     * Creates a new Specifications object
     *
     * @param string $operator The specified operator, as one of the Operator::* constants
     * @param string $valueFormat The accepted value format, either {@see self::FORMAT_ARRAY} or {@see self::FORMAT_SINGLE}
     * @param int $valueTypes The supported value types, as a bit field of the {@see self::TYPES_*} constants
     * @param int $valueCount The required number of values, when the accepted format is {@see self::FORMAT_ARRAY}
     */
    public function __construct( $operator, $valueFormat, $valueTypes = null, $valueCount = null )
    {
        $this->operator = $operator;
        $this->valueFormat = $valueFormat;
        $this->valueTypes = $valueTypes;
        $this->valueCount = $valueCount;
    }
}
