<?php
/**
 * Contains Unauthorized Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use Exception as PHPException;

/**
 * Unauthorized Exception implementation
 *
 * Use:
 *   throw new Unauthorized( 'Content' );
 *
 */
class Unauthorized extends Http
{
    /**
     * Generates: Login required to get access to '{$what}'
     *
     * @param string $what
     * @param \Exception|null $previous
     */
    public function __construct( $what, PHPException $previous = null )
    {
        parent::__construct( "Login required to get access to '{$what}'", self::UNAUTHORIZED, $previous );
    }
}
