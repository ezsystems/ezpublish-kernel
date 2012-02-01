<?php
/**
 * Contains Property Not Found Exception implementation
 *
 * @copyright Copyright (C) 2012 andrerom & eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Exception;
use eZ\Publish\Core\Base\Exception,
    Exception as PHPException,
    InvalidArgumentException;

/**
 * Property Not Found Exception implementation
 *
 * Use:
 *   throw new PropertyNotFound( 'nodeId', __CLASS__ );
 *
 */
class PropertyNotFound extends InvalidArgumentException implements Exception
{
    /**
     * Generates: Property '{$propertyName}' not found
     *
     * @param string $propertyName
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param PHPException|null $previous
     */
    public function __construct( $propertyName, $className = null, PHPException $previous = null )
    {
        if ( $className === null )
            parent::__construct( "Property '{$propertyName}' not found", 0, $previous );
        else
            parent::__construct( "Property '{$propertyName}' not found on class '{$className}'", 0, $previous );
    }
}
