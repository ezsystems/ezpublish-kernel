<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Subtree class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use InvalidArgumentException;

/**
 * Criterion that matches Location that belongs to a given (list of) Subtree(s)
 *
 * Location will be matched if it in at least one of the given subtree path strings
 */
class Subtree extends Location
{
    /**
     * Creates a new Subtree criterion
     *
     * @param string|string[] $value an array of subtree path strings, eg: /1/2/
     *
     * @throws InvalidArgumentException if a non path string is given
     * @throws InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $value )
    {
        if ( is_array( $value ) )
        {
            if ( !isset( $value[0][0] ) || $value[0][0] !== '/' )
                throw new InvalidArgumentException( "\$value array values must follow the pathString format, eg /1/2/" );
        }
        else if ( !isset( $value[0] ) || $value[0] !== '/' )
        {
            throw new InvalidArgumentException( "\$value array values must follow the pathString format, eg /1/2/" );
        }

        parent::__construct( null, null, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_STRING
            )
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $value );
    }
}
