<?php
/**
 * Contains Bad Request Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use Exception as PHPException;

/**
 * Bad Request Exception implementation
 *
 * 10.4.1 400 Bad Request
 *
 * The request could not be understood by the server due to malformed syntax. The client SHOULD NOT repeat the request
 * without modifications.
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
     * @param \Exception|null $previous
     */
    public function __construct( $missing, $from, PHPException $previous = null )
    {
        parent::__construct( "Bad request, missing {$missing} in {$from}", self::BAD_REQUEST, $previous );
    }
}
