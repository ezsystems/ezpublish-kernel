<?php
/**
 * Contains Not Found Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use Exception as PHPException;

/**
 * Not Found Exception implementation
 *
 * Use:
 *   throw new NotFound( 'Content', 42 );
 *
 */
class NotFound extends Http
{
    /**
     * Generates: Could not find '{$what}' with identifier '{$identifier}'
     *
     * @param string $what
     * @param mixed $identifier
     * @param \Exception|null $previous
     */
    public function __construct( $what, $identifier, PHPException $previous = null )
    {
        parent::__construct( "Could not find '{$what}' with identifier '" . var_export( $identifier, true ) . "'", self::NOT_FOUND, $previous );
    }
}
