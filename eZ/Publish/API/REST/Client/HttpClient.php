<?php
/**
 * File containing the HttpClient interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client;

/**
 * Interface for Http Client implementations
 */
interface HttpClient
{
    /**
     * Execute a HTTP request to the remote server
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param string $path
     * @param string $body
     * @param array $headers
     * @return mixed
     */
    public function request( $method, $path, $body = null, array $headers = array() );
}
