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
 * Invalid Argument NotEmpty implementation
 *
 * @use: throw new NotEmpty( 'Group', $id );
 *
 */
class NotEmpty extends InvalidArgumentException implements Exception
{
    /**
     * Generates: Invalid argument, {$container} with identifier '{$identifier}' is not empty
     *
     * @param string $container
     * @param mixed $identifier
     * @param PHPException|null $previous
     */
    public function __construct( $container, $identifier, PHPException $previous = null )
    {
        parent::__construct( "Invalid argument, {$container} with identifier '{$identifier}' is not empty", 0, $previous );
    }
}
