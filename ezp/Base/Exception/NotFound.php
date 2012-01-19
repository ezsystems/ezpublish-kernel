<?php
/**
 * Contains Not Found Exception implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Exception;
use Exception as PHPException;

/**
 * Not Found Exception implementation
 *
 * 10.4.5 404 Not Found
 *
 * The server has not found anything matching the Request-URI. No indication is given of whether the condition is
 * temporary or permanent. The 410 (Gone) status code SHOULD be used if the server knows, through some internally
 * configurable mechanism, that an old resource is permanently unavailable and has no forwarding address.
 * This status code is commonly used when the server does not wish to reveal exactly why the request has been refused,
 * or when no other response is applicable.
 *
 * Use:
 *   throw new NotFound( 'Content', 42 );
 *
 */
class NotFound extends Http
{
    /**
     * What was not found
     * @var string
     */
    public $what;

    /**
     * Identifier of what was not found
     * @var mixed
     */
    public $identifier;

    /**
     * Generates: Could not find '{$what}' with identifier '{$identifier}'
     *
     * @param string $what
     * @param mixed $identifier
     * @param \Exception|null $previous
     */
    public function __construct( $what, $identifier, PHPException $previous = null )
    {
        $this->what = $what;
        $this->identifier = var_export( $identifier, true );
        parent::__construct( "Could not find '{$what}' with identifier '" . $this->identifier . "'", self::NOT_FOUND, $previous );
    }
}
