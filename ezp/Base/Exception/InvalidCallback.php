<?php
/**
 * Contains Invalid Callback Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use ezp\Base\Exception,
    Exception as PHPException,
    BadFunctionCallException;

/**
 * Invalid Callback Exception implementation
 *
 * Use:
 *   throw new InvalidCallback( array( 'Class', 'nonExistingFunction' ) );
 *
 */
class InvalidCallback extends BadFunctionCallException implements Exception
{
    /**
     * Generates: Invalid callback: $callback
     *
     * @param string|array $callback
     * @param PHPException|null $previous
     */
    public function __construct( $callback, PHPException $previous = null )
    {
        parent::__construct( "Invalid callback: " . var_export( $callback, true ), 0, $previous );
    }
}
