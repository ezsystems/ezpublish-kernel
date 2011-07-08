<?php
/**
 * Contains Method Not Found Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\Base\Exception;

/**
 * Method Not Found Exception implementation
 *
 * Use:
 *   throw new MethodNotFound( 'getRelations', __CLASS__ );
 *
 * @package ezp
 * @subpackage base
 */
class MethodNotFound extends \BadMethodCallException implements \ezp\Base\Exception
{
    /**
     * Generates: Method '{$method}' not found
     *
     * @param string $method
     * @param string|null $className Optionally to specify class in abstract/parent classes
     * @param \Exception|null $previous
     */
    public function __construct( $method, $className = null, \Exception $previous = null )
    {
         if ( $className === null )
            parent::__construct( "Method '{$method}' not found", 0, $previous );
        else
            parent::__construct( "Method '{$method}' not found on class '{$className}'", 0, $previous );
    }
}