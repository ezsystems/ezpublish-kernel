<?php
/**
 * Contains NotFoundWithType Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use ezp\Base\Exception,
    ezp\Base\Exception\NotFound,
    Exception as PHPException;

/**
 * NotFoundWithType Exception implementation
 *
 * @use: throw new NotFoundWithType( 'User Group', $id );
 *
 */
class NotFoundWithType extends NotFound implements Exception
{
    /**
     * Generates: Could not find 'Content of type {$type}' with identifier '{$identifier}'
     *
     * @param string $type
     * @param mixed $identifier
     * @param \Exception|null $previous
     */
    public function __construct( $type, $identifier, PHPException $previous = null )
    {
        parent::__construct( "Content of type {$type}", $identifier, $previous );
    }
}
