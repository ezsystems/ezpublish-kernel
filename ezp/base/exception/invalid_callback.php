<?php
/**
 * Contains Invalid Callback Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

namespace ezp\base\Exception;

/**
 * Invalid Callback Exception implementation
 *
 * Use:
 *   throw new InvalidCallback( array( 'Class', 'nonExistingFunction' ) );
 *
 * @package ezp
 * @subpackage base
 */
class InvalidCallback extends \BadFunctionCallException implements \ezp\base\Exception
{
    /**
     * Generates: Invalid callback: $callback
     *
     * @param string|array $callback
     * @param \Exception|null $previous
     */
    public function __construct( $callback, \Exception $previous = null )
    {
        parent::__construct( "Invalid callback: " . var_export( $callback, true), 0, $previous );
    }
}