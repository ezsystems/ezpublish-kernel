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
class InvalidArgumentValue extends InvalidArgumentException implements Exception
{
    /**
     * Generates: Argument '{$argumentName}' got invalid value '{$value}'
     *
     * @param string $argumentName
     * @param mixed $value
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param PHPException|null $previous
     */
    public function __construct( $argumentName, $value, $className = null, PHPException $previous = null )
    {
        parent::__construct(
            "Argument '{$argumentName}' got invalid value '" . var_export( $value, true ) . "'" .
            ( $className !== null ? " on class '{$className}'" : "" ),
            0,
            $previous
        );
    }
}
