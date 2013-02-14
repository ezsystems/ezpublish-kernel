<?php
/**
 * Contains Invalid Argument Type Exception implementation
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exceptions;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use Exception;

/**
 * Invalid Argument Type Exception implementation
 *
 * @use: throw new InvalidArgument( 'nodes', 'array' );
 */
class InvalidArgumentValue extends InvalidArgumentException
{
    /**
     * Generates: "Argument '{$argumentName}' is invalid: '{$value}' is wrong value[ in class '{$className}']"
     *
     * @param string $argumentName
     * @param mixed $value
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $argumentName, $value, $className = null, Exception $previous = null )
    {
        parent::__construct(
            $argumentName,
            "'" . var_export( $value, true ) . "' is wrong value" . ( $className ? " in class '{$className}'" : "" ),
            $previous
        );
    }
}
