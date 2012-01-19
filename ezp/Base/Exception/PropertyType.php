<?php
/**
 * Contains Property Type Exception implementation
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
 * Property Type Exception implementation
 *
 * Use:
 *   throw new PropertyType( 'nodeId', 'int', __CLASS__ );
 *
 */
class PropertyType extends InvalidArgumentException implements Exception
{
    /**
     * Generates: Property '{$propertyName}' can only be of type '{$type}'
     *
     * @param string $propertyName
     * @param string $type
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $propertyName, $type, $className = null, PHPException $previous = null )
    {
        if ( $className === null )
            parent::__construct( "Property '{$propertyName}' must be of type '{$type}'", 0, $previous );
        else
            parent::__construct( "Property '{$propertyName}' must be of type '{$type}' on class '{$className}'", 0, $previous );
    }
}
