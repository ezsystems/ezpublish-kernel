<?php
/**
 * File containing the BasicAuth authentication HttpClient
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\HttpClient\Authentication;

use eZ\Publish\Core\REST\Client\HttpClient;
use eZ\Publish\Core\REST\Common\Message;

/**
 * Interface for Http Client implementations
 */
class BasicAuth implements HttpClient
{
    /**
     * Inner HTTP client, performing the actual requests.
     *
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    protected $innerClient;

    /**
     * User name for Basic Auth
     *
     * @var string
     */
    protected $username;

    /**
     * Password for Basic Auth
     *
     * @var string
     */
    protected $password;

    /**
     * Creates a new Basic Auth HTTP client
     *
     * @param \eZ\Publish\Core\REST\Client\HttpClient $innerClient
     * @param string $username
     * @param string $password
     */
    public function __construct( HttpClient $innerClient, $username, $password )
    {
        $this->innerClient = $innerClient;
        $this->username    = $username;
        $this->password    = $password;
    }

    /**
     * Execute a HTTP request to the remote server
     *
     * Returns the result from the remote server. The client sets the correct
     * headers for Basic Auth into the $message transmitted to the inner
     * client.
     *
     * @param string $method
     * @param string $path
     * @param \eZ\Publish\Core\REST\Common\Message $message
     *
     * @return \eZ\Publish\Core\REST\Common\Message
     */
    public function request( $method, $path, Message $message = null )
    {
        if ( $message === null )
        {
            $message = new Message();
        }
        $message->headers['Authorization'] = sprintf(
            'Basic %s',
            base64_encode(
                sprintf(
                    '%s:%s',
                    $this->username,
                    $this->password
                )
            )
        );
        return $this->innerClient->request( $method, $path, $message );
    }
}
