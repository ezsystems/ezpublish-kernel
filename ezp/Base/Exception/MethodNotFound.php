<?php
/**
 * Contains Method Not Found Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use ezp\Base\Exception,
    Exception as PHPException,
    BadMethodCallException;

/**
 * Method Not Found Exception implementation
 *
 * Use:
 *   throw new MethodNotFound( 'getRelations', __CLASS__ );
 *
 */
class MethodNotFound extends BadMethodCallException implements Exception
{
    /**
     * Generates: Method '{$method}' not found
     *
     * @param string $method
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param PHPException|null $previous
     */
    public function __construct( $method, $className = null, PHPException $previous = null )
    {
         if ( $className === null )
            parent::__construct( "Method '{$method}' not found", 0, $previous );
        else
            parent::__construct( "Method '{$method}' not found on class '{$className}'", 0, $previous );
    }
}
