<?php
/**
 * Contains Unauthorized Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use Exception as PHPException;

/**
 * Unauthorized Exception implementation
 *
 * 10.4.2 401 Unauthorized
 *
 * The request requires user authentication. The response MUST include a WWW-Authenticate header field (section 14.47)
 * containing a challenge applicable to the requested resource. The client MAY repeat the request with a suitable
 * Authorization header field (section 14.8). If the request already included Authorization credentials, then the 401
 * response indicates that authorization has been refused for those credentials. If the 401 response contains the same
 * challenge as the prior response, and the user agent has already attempted authentication at least once, then the user
 * SHOULD be presented the entity that was given in the response, since that entity might include relevant diagnostic
 * information. HTTP access authentication is explained in "HTTP Authentication: Basic and Digest Access
 * Authentication".
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
