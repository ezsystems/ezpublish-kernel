<?php
/**
 * File containing a HttpClient implementation
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\HttpClient;
use eZ\Publish\API\REST\Client\HttpClient;
use eZ\Publish\API\REST\Client\Sessionable;
use eZ\Publish\API\REST\Common\Message;

/**
 * HTTP client that maintains a test session
 */
class TestSession implements HttpClient, Sessionable
{
    /**
     * Inner HTTP client.
     *
     * @var \eZ\Publish\API\REST\Client\HttpClient
     */
    protected $innerClient;

    /**
     * Session ID
     *
     * @var string
     */
    protected $sessionId;

    /**
     * Creates a new session maintaining client based on $innerClient
     *
     * @param HttpClient $innerClient
     * @return void
     */
    public function __construct( HttpClient $innerClient )
    {
        $this->innerClient = $innerClient;
    }

    /**
     * Set the session ID to use
     *
     * @param string $id
     * @return void
     */
    public function setSession( $id )
    {
        $this->sessionId = $id;
    }

    /**
     * Execute a HTTP request to the remote server
     *
     * Returns the result from the remote server.
     *
     * @param string $method
     * @param string $path
     * @param Message $message
     * @return Message
     */
    public function request( $method, $path, Message $message = null )
    {
        $message = $message ?: new Message();

        if ( $this->sessionId !== null && !isset( $message->headers['X-Test-Session'] ) )
        {
            $message->headers['X-Test-Session'] = $this->sessionId;
        }
        return $this->innerClient->request( $method, $path, $message );
    }
}
