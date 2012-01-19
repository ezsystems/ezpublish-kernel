<?php
/**
 * Contains Forbidden Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use Exception as PHPException;

/**
 * Forbidden Exception implementation
 *
 * 10.4.4 403 Forbidden
 *
 * The server understood the request, but is refusing to fulfill it. Authorization will not help and the request SHOULD
 * NOT be repeated. If the request method was not HEAD and the server wishes to make public why the request has not been
 * fulfilled, it SHOULD describe the reason for the refusal in the entity. If the server does not wish to make this
 * information available to the client, the status code 404 (Not Found) can be used instead.
 *
 * Use:
 *   throw new Forbidden( 'Content', 'create' );
 *
 */
class Forbidden extends Http
{
    /**
     * Generates: User does not have access to $action '{$type}'
     *
     * @param string $type
     * @param string $action
     * @param \Exception|null $previous
     */
    public function __construct( $type, $action, PHPException $previous = null )
    {
        parent::__construct( "User did not have access to {$action} '{$type}'", self::FORBIDDEN, $previous );
    }
}
