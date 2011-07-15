<?php
/**
 * File containing the Criterion class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\Content\Criterion\OperatorSpecifications,
    InvalidArgumentException;

/**
 */
abstract class Criterion
{
    /**
     * Performs operator validation based on the Criterion specifications returned by {@see getSpecifications()}
     * @param string|null $target
     * @param string|null $operator
     * @param string[]|int[]|int|string $value
     *
     * @todo Add a dedicated exception
     * @throws InvalidArgumentException if the provided operator isn't supported
     */
    public function __construct( $target, $operator, $value )
    {
        $operatorFound = false;

        // we loop on each specified operator.
        // If the provided operator ain't found, an exception will be thrown at the end
        foreach ( $this->getSpecifications() as $operatorSpecifications )
        {
            if ( $operatorSpecifications->operator != $operator )
            {
                continue;
            }
            $operatorFound = true;

            // input format check (single/array)
            switch( $operatorSpecifications->valueFormat )
            {
                case OperatorSpecifications::FORMAT_SINGLE:
                    if ( is_array( $value ) )
                    {
                        throw new InvalidArgumentException( "The Criterion expects a single value" );
                    }
                    break;

                case OperatorSpecifications::FORMAT_ARRAY:
                    if ( !is_array( $value ) )
                    {
                        throw new InvalidArgumentException( "The criterion expects an array of values" );
                    }
                    break;
            }

            // input value check
            if ( $operatorSpecifications->valueTypes !== null )
            {
                $callback = $this->getValueTypeCheckCallback( $operatorSpecifications->valueTypes );
                foreach ( $value as $item )
                {
                    if ( $callback( $item ) === false )
                    {
                        throw new InvalidArgumentException( "Unsupported value type ('$item')" );
                    }
                }
            }
        }

        // Operator wasn't found in the criterion specifications
        if ( $operatorFound == false )
        {
            throw new InvalidArgumentException( "Operator $operator isn't supported by the Criterion " . get_class( $this ) );
        }

        $this->operator = $operator;
        $this->value = $value;
        $this->target = $target;
    }

    /**
     * Returns a callback that checks the values types depending on the operator specifications
     * @param array $valueTypes The accepted values, as an array of OperatorSpecifications::TYPE_* constants
     * @return callback
     */
    private function getValueTypeCheckCallback( $valueTypes )
    {
        $code = '';

        // the callback code will return true as soon as an accepted value type is found
        if ( in_array( OperatorSpecifications::TYPE_INTEGER ) )
        {
            $code .= 'if ( is_numeric( $v ) ) return true;';
        }
        if ( in_array( OperatorSpecifications::TYPE_STRING ) )
        {
            $code .= 'if ( is_string( $v ) ) return true;';
        }
        if ( in_array( OperatorSpecifications::TYPE_BOOL ) )
        {
            $code .= 'if ( is_bool( $v ) ) return true;';
        }
        $code = 'return false;';
        return create_function( '$v', $code );
    }

    /**
     * The operator used by the Criterion
     * @var string
     */
    public $operator;

    /**
     * The value(s) matched by the criteria
     * @var array(int|string)
     */
    public $value;

    /**
     * The target used by the criteria (field, metadata...)
     * @var string
     */
    public $target;
}
?>
