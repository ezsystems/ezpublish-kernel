<?php
/**
 * Contains ReadOnly Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Exception;
use ezp\Base\Exception,
    Exception as PHPException,
    InvalidArgumentException;

/**
 * PathExists Exception implementation.
 * Used when a path should not exist for an operation to be executed.
 *
 * @use: throw new PathExists( 'path/to/existing/file.ext' );
 */
class PathExists extends InvalidArgumentException implements Exception
{
    /**
     * Generates: {$path} already exists
     *
     * @param string $path The path that already exists and conflicts
     * @param PHPException|null $previous
     */
    public function __construct( $path, PHPException $previous = null )
    {
        parent::__construct( "{$path} already exists", 0, $previous );
    }
}
