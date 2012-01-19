<?php
/**
 * Contains Invalid Argument Type Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use ezp\Base\Exception,
    Exception as PHPException,
    InvalidArgumentException;

/**
 * Invalid Argument Type Exception implementation
 *
 * @use: throw new InvalidArgument( 'nodes', 'array' );
 *
 */
class InvalidArgumentType extends InvalidArgumentException implements Exception
{
    /**
     * Generates: Argument '{$argumentName}' can only be of type '{$accepts}'
     *
     * @param string $argumentName
     * @param string $accepts Type that are accepted
     * @param mixed $value Optionally to specify what value you got
     * @param \Exception|null $previous
     */
    public function __construct( $argumentName, $accepts, $value = null, PHPException $previous = null )
    {
        if ( $value === null )
        {
            parent::__construct( "Argument '{$argumentName}' can only be of type '{$accepts}'", 0, $previous );
        }
        else
        {
            $type = ( is_object( $value ) ? get_class( $value ): gettype( $value ) );
            parent::__construct( "Argument '{$argumentName}' can only be of type '{$accepts}', got: '{$type}'", 0, $previous );
        }
    }
}
