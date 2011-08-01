<?php
/**
 * Contains Bad Request Exception implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use Exception as PHPException;

/**
 * Bad Request Exception implementation
 *
 * Use:
 *   throw new BadRequest( 'Oauth Token', 'http header' );
 *
 */
class BadRequest extends Http
{
    /**
     * Generates: Bad request, missing {$missing} in {$from}
     *
     * @param string $missing
     * @param string $from
     * @param PHPException|null $previous
     */
    public function __construct( $missing, $from, PHPException $previous = null )
    {
        parent::__construct( "Bad request, missing {$missing} in {$from}", self::BAD_REQUEST, $previous );
    }
}
