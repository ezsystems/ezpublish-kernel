<?php
/**
 * File containing the ContentValidationException class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\HttpClient;

/**
 * HTTPClient connection exception
 */
class ConnectionException extends \RuntimeException
{
    /**
     * Constructor
     *
     * @param string $server
     * @param string $path
     * @param string $method
     */
    public function __construct( $server, $path, $method )
    {
        parent::__construct(
            "Could not connect to server '$server' and retrieve '$path' with '$method'."
        );
    }
}
