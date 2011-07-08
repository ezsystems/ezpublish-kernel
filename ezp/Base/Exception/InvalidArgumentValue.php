<?php
/**
 * Contains Invalid Argument Type Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Exception;

/**
 * Invalid Argument Type Exception implementation
 *
 * @use: throw new InvalidArgument( 'nodes', 'array' );
 *
 * @package ezp
 * @subpackage base
 */
class InvalidArgumentValue extends \InvalidArgumentException implements \ezp\Base\Exception
{
    /**
     * Generates: Argument '{$argumentName}' got invalid value '{$value}'
     *
     * @param string $argumentName
     * @param mixed $value
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $argumentName, $value, $className = null, \Exception $previous = null )
    {
        if ( $className === null )
            parent::__construct( "Argument '{$argumentName}' got invalid value '{$value}'", 0, $previous );
        else
            parent::__construct( "Argument '{$argumentName}' got invalid value '{$value}' on class '{$className}'", 0, $previous );
    }
}