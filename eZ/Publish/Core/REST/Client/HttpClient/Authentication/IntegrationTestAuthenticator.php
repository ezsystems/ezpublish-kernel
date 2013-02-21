<?php
/**
 * File containing the IntegrationTestAuthenticator authentication HttpClient
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\HttpClient\Authentication;

use eZ\Publish\Core\REST\Client\HttpClient;
use eZ\Publish\Core\REST\Client\Sessionable;
use eZ\Publish\Core\REST\Common\Message;

/**
 * Authenticator used in integration tests.
 *
 * Note: DO NOT USE THIS IN PRODUCTION.
 */
class IntegrationTestAuthenticator implements HttpClient, Sessionable
{
    /**
     * Inner HTTP client, performing the actual requests.
     *
     * @var \eZ\Publish\Core\REST\Client\HttpClient
     */
    protected $innerClient;

    /**
     * User ID to be sent to the server.
     *
     * @var mixed
     */
    protected $userId;

    /**
     * Session ID
     *
     * @var string
     */
    protected $sessionId;

    /**
     * Creates a new Integration Test Authenticator
     *
     * @param \eZ\Publish\Core\REST\Client\HttpClient $innerClient
     * @param mixed $userId
     */
    public function __construct( HttpClient $innerClient, $userId = 14 )
    {
        $this->innerClient = $innerClient;
        $this->userId      = $userId;
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
        $message = $message ?: new Message();

        $message->headers['X-Test-User'] = $this->userId;

        if ( $this->sessionId !== null && !isset( $message->headers['X-Test-Session'] ) )
        {
            $message->headers['X-Test-Session'] = $this->sessionId;
        }

        return $this->innerClient->request( $method, $path, $message );
    }

    /**
     * Sets the user ID submitted to the server.
     *
     * @param mixed $userId
     */
    public function setUserId( $userId )
    {
        $this->userId = $userId;
    }

    /**
     * Set the session ID to use
     *
     * @param string $id
     */
    public function setSession( $id )
    {
        $this->sessionId = $id;
    }
}
